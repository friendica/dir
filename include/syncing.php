<?php

// Debug stuff.
ini_set('display_errors', 1);
ini_set('log_errors','0');
error_reporting(E_ALL^E_NOTICE);

$start_syncing = time();

//Startup.
require_once('boot.php');
$a = new App;

//Create a simple log function for CLI use.
$verbose = $argv[1] === 'verbose';
$msg = function($message, $fatal=false)use($verbose){
	if($verbose || $fatal) echo($message.PHP_EOL);
	logger($message);
	if($fatal) exit(1);
};

//Config.
require_once(".htconfig.php");

//No pushing? Leave... because we haven't implemented pulling yet.
if(!$a->config['syncing']['enable_pushing']){
	$msg('No push support enabled in your settings.', true);
}

//Connect the DB.
require_once("dba.php");
$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);

//Find our targets.
$targets = q("SELECT * FROM `sync-targets` WHERE `push`=b'1'");
if(!count($targets)) $msg('No targets.', true); //No targets, means no work.

//Get our batch of URL's.
$batch = q("SELECT * FROM `sync-queue` LIMIT %u", intval($a->config['syncing']['max_push_items']));
if(!count($batch)) $msg('Empty queue.', true); //No batch, means no work.

//Close the connection for now. Process forking and DB connections are not the best of friends.
$db->getdb()->close();

//Create a thread for each target we want to serve push messages to.
//No good creating more, because it would stress their server too much.
$threadc = count($targets);
$threads = array();

//Do we only have 1 target? No need for threads.
if($threadc === 1){
	//Pretend to be worker #1.
	$pid = 0;
	$i = 0;
	$main = true;
	$msg('No threads needed. Only one pushing target.');
}

//When we need threads.
else{
	
	//POSIX threads only.
	if(!function_exists('pcntl_fork')){
		$msg('Error: no pcntl_fork support. Are you running a different OS? Report an issue please.', true);
	}
	
	//Debug...
	$items = count($batch);
	$msg("Creating $threadc push threads for $items items.");
	
	//Loop while we need more threads.
	for($i = 0; $i < $threadc; $i++){
		
		$pid = pcntl_fork();
		if($pid === -1) $msg('Error: something went wrong with the fork. '.pcntl_strerror(), true);
		
		//You're a child, go do some labor!
		if($pid === 0) break;
		
		//Store the list of PID's.
		if($pid > 0) $threads[] = $pid;
		
	}
	
	//Are we the main thread?
	$main = $pid !== 0;
	
}

//The work for child processes.
if($pid === 0){
	
	//Lets be nice, we're only doing a background job here...
	pcntl_setpriority(5);
	
	//Find our target's submit URL.
	$submit = $targets[$i]['base_url'].'/submit';
	
	foreach($batch as $item){
		set_time_limit(30); //This should work for 1 submit.
		$msg("Submitting {$item['url']} to $submit");
		fetch_url($submit.'?url='.bin2hex($item['url']));
	}
	
}

//The main process.
if($main){
	
	//Wait for all child processes.
	$all_good = true;
	foreach($threads as $pid){
		pcntl_waitpid($pid, $status);
		if($status !== 0){
			$all_good = false;
			$msg("Bad process return value $pid:$status");
		}
	}
	
	//If we did not have any "threading" problems.
	if($all_good){
		
		//Reconnect
		$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
		
		//Create a query for deleting this queue.
		$where = array();
		foreach($batch as $item) $where[] = dbesc($item['url']);
		$where = "WHERE `url` IN ('".implode("', '", $where)."')";
		
		//Remove the items from queue.
		q("DELETE FROM `sync-queue` $where LIMIT %u", intval($a->config['syncing']['max_push_items']));
		$msg('Removed items from queue.');
		
	}
	
	//Log the time it took.
	$time = time() - $start_syncing;
	$msg("Syncing completed. Took $time seconds.");
	
}