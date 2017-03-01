<?php
	
	// Debug stuff.
	// ini_set('display_errors', 1);
	// ini_set('log_errors','0');
	error_reporting(E_ALL^E_NOTICE);
	
	$start_maintain = time();
	
	$verbose = $argv[1] === 'verbose';
	
	//Startup.
	require_once('boot.php');
	$a = new App;
	
	//Config and DB.
	require_once(".htconfig.php");
	require_once("dba.php");
	$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
	
	//Get our set of items. Youngest items first, after the threshold.
	//This may be counter-intuitive, but is to prevent items that fail to update from blocking the rest.
	$res = q(
		"SELECT `id`, `homepage`, `censored` FROM `profile` WHERE `updated` < '%s' ORDER BY `updated` DESC LIMIT %u",
		dbesc(date('Y-m-d H:i:s', time()-$a->config['maintenance']['min_scrape_delay'])),
		intval($a->config['maintenance']['max_scrapes'])
	);
	
	//Nothing to do.
	if(!$res || !count($res)){
		exit;
	}
	
	//Close DB here. Threads need their private connection.
	$db->getdb()->close();
	
	//We need the scraper.
	require_once('include/submit.php');
	
	//POSIX threads only.
	if(!function_exists('pcntl_fork')){
		logger('Error: no pcntl_fork support. Are you running a different OS? Report an issue please.');
		die('Error: no pcntl_fork support. Are you running a different OS? Report an issue please.');
	}
	
	//Create the threads we need.
	$items = count($res);
	$threadc = min($a->config['maintenance']['threads'], $items); //Don't need more threads than items.
	$threads = array();
	
	//Debug...
	if($verbose) echo("Creating $threadc maintainer threads for $items profiles.".PHP_EOL);
	logger("Creating $threadc maintainer threads for $items profiles.");
	
	for($i = 0; $i < $threadc; $i++){
		
		$pid = pcntl_fork();
		if($pid === -1){
			if($verbose) echo('Error: something went wrong with the fork. '.pcntl_strerror());
			logger('Error: something went wrong with the fork. '.pcntl_strerror());
			die('Error: something went wrong with the fork. '.pcntl_strerror());
		}
		
		//You're a child, go do some labor!
		if($pid === 0) break;
		
		//Store the list of PID's.
		if($pid > 0) $threads[] = $pid;
		
	}
	
	//The work for child processes.
	if($pid === 0){
		
		//Lets be nice, we're only doing maintenance here...
		pcntl_setpriority(5);
		
		//Get personal DBA's.
		$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
		
		//Get our (round-robin) workload from the DB results.
		$myIndex = $i+1;
		$workload = array();
		while(isset($res[$i])){
			$entry = $res[$i];
			$workload[] = $entry;
			$ids[] = $entry['id'];
			$i+=$threadc;
		}
		
		while(count($workload)){
			$entry = array_pop($workload);
			set_time_limit(20); //This should work for 1 submit.
			if($verbose) echo "Submitting ".$entry['homepage'].PHP_EOL;
			run_submit($entry['homepage']);
		}
		
		exit;
		
	}
	
	//The main process.
	else{
		foreach($threads as $pid){
			pcntl_waitpid($pid, $status);
			if($status !== 0){
				if($verbose) echo "Bad process return value $pid:$status".PHP_EOL;
				logger("Bad process return value $pid:$status");
			}
		}
		$time = time() - $start_maintain;
		if($verbose) echo("Maintenance completed. Took $time seconds.".PHP_EOL);
		logger("Maintenance completed. Took $time seconds.");
	}
