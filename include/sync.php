<?php

/**
 * Pull this URL to our pulling queue.
 * @param  string $url
 * @return void
 */
function sync_pull($url)
{
  
  global $a;
  
  //If we support it that is.
  if($a->config['syncing']['enable_pulling']){
    q("INSERT INTO `sync-pull-queue` (`url`) VALUES ('%s')", dbesc($url));
  }
  
}

/**
 * Push this URL to our pushing queue as well as mark it as modified using sync_mark.
 * @param  string $url
 * @return void
 */
function sync_push($url)
{
  
  global $a;
  
  //If we support it that is.
  if($a->config['syncing']['enable_pushing']){
    q("INSERT INTO `sync-push-queue` (`url`) VALUES ('%s')", dbesc($url));
  }
  
  sync_mark($url);
  
}

/**
 * Mark a URL as modified in some way or form.
 * This will cause anyone that pulls our changes to see this profile listed.
 * @param  string $url
 * @return void
 */
function sync_mark($url)
{
  
  global $a;
  
  //If we support it that is.
  if(!$a->config['syncing']['enable_pulling']){
    return;
  }
  
  $exists = count(q("SELECT * FROM `sync-timestamps` WHERE `url`='%s'", dbesc($url)));
  
  if(!$exists)
    q("INSERT INTO `sync-timestamps` (`url`, `modified`) VALUES ('%s', NOW())", dbesc($url));
  else
    q("UPDATE `sync-timestamps` SET `modified`=NOW() WHERE `url`='%s'", dbesc($url));
  
}

/**
 * For a single fork during the push jobs.
 * Takes a lower priority and pushes a batch of items.
 * @param  string $target A sync-target database row.
 * @param  array  $batch  The batch of items to submit.
 * @return void
 */
function push_worker($target, $batch)
{
  
  //Lets be nice, we're only doing a background job here...
  pcntl_setpriority(5);
  
  //Find our target's submit URL.
  $submit = $target['base_url'].'/submit';
  
  foreach($batch as $item){
    set_time_limit(30); //This should work for 1 submit.
    msg("Submitting {$item['url']} to $submit");
    fetch_url($submit.'?url='.bin2hex($item['url']));
  }
  
}

/**
 * Gets an array of push targets.
 * @return array Push targets.
 */
function get_push_targets(){
  return q("SELECT * FROM `sync-targets` WHERE `push`=b'1'");
}

/**
 * Gets a batch of URL's to push.
 * @param object $a The App instance.
 * @return array Batch of URL's.
 */
function get_push_batch($a){
  return q("SELECT * FROM `sync-push-queue` LIMIT %u", intval($a->config['syncing']['max_push_items']));
}

/**
 * Gets the push targets as well as a batch of URL's for a pushing job.
 * @param object $a The App instance.
 * @return list($targets, $batch) A list of both the targets array and batch array.
 */
function get_pushing_job($a)
{
  
  //When pushing is requested...
  if(!!$a->config['syncing']['enable_pushing']){
    
    //Find our targets.
    $targets = get_push_targets();
    
    //No targets?
    if(!count($targets)){
      msg('Pushing enabled, but no push targets.');
      $batch = array();
    }
    
    //If we have targets, get our batch.
    else{
      $batch = get_push_batch($a);
      if(!count($batch)) msg('Empty pushing queue.'); //No batch, means no work.
    }
    
  }

  //No pushing if it's disabled.
  else{
    $targets = array();
    $batch = array();
  }
  
  return array($targets, $batch);
  
}

/**
 * Runs a pushing job, creating a thread for each target.
 * @param  array  $targets Pushing targets.
 * @param  array  $batch   Batch of URL's to push.
 * @param  string $db_host    DB host to connect to.
 * @param  string $db_user    DB user to connect with.
 * @param  string $db_pass    DB pass to connect with.
 * @param  mixed  $db_data    Nobody knows.
 * @param  mixed  $install    Maybe a boolean.
 * @return void
 */
function run_pushing_job($targets, $batch, $db_host, $db_user, $db_pass, $db_data, $install)
{
  
  //Create a thread for each target we want to serve push messages to.
  //Not good creating more, because it would stress their server too much.
  $threadc = count($targets);
  $threads = array();
  
  //Do we only have 1 target? No need for threads.
  if($threadc === 1){
    msg('No threads needed. Only one pushing target.');
    push_worker($targets[0], $batch);
  }
  
  //When we need threads.
  elseif($threadc > 1){
    
    //POSIX threads only.
    if(!function_exists('pcntl_fork')){
      msg('Error: no pcntl_fork support. Are you running a different OS? Report an issue please.', true);
    }
    
    //Debug...
    $items = count($batch);
    msg("Creating $threadc push threads for $items items.");
    
    //Loop while we need more threads.
    for($i = 0; $i < $threadc; $i++){
      
      $pid = pcntl_fork();
      if($pid === -1) msg('Error: something went wrong with the fork. '.pcntl_strerror(), true);
      
      //You're a child, go do some labor!
      if($pid === 0){push_worker($targets[$i], $batch); exit;}
      
      //Store the list of PID's.
      if($pid > 0) $threads[] = $pid;
      
    }
    
  }
  
  //Wait for all child processes.
  $theading_problems = false;
  foreach($threads as $pid){
    pcntl_waitpid($pid, $status);
    if($status !== 0){
      $theading_problems = true;
      msg("Bad process return value $pid:$status");
    }
  }
  
  //If we did not have any "threading" problems.
  if(!$theading_problems){
    
    //Reconnect
    global $db;
    $db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
    
    //Create a query for deleting this queue.
    $where = array();
    foreach($batch as $item) $where[] = dbesc($item['url']);
    $where = "WHERE `url` IN ('".implode("', '", $where)."')";
    
    //Remove the items from queue.
    q("DELETE FROM `sync-push-queue` $where LIMIT %u", count($batch));
    msg('Removed items from push queue.');
    
  }
  
}

/**
 * Gets a batch of URL's to push.
 * @param object $a The App instance.
 * @return array Batch of URL's.
 */
function get_queued_pull_batch($a){
  //Randomize this, to prevent scraping the same servers too much or dead URL's.
  $batch = q("SELECT * FROM `sync-pull-queue` ORDER BY RAND() LIMIT %u", intval($a->config['syncing']['max_pull_items']));
  msg(sprintf('Pulling %u items from queue.', count($batch)));
  return $batch;
}

/**
 * Gets an array of pull targets.
 * @return array Pull targets.
 */
function get_pull_targets(){
  return q("SELECT * FROM `sync-targets` WHERE `pull`=b'1'");
}

/**
 * Gets a batch of URL's to push.
 * @param object $a The App instance.
 * @return array Batch of URL's.
 */
function get_remote_pull_batch($a)
{
  
  //Find our targets.
  $targets = get_pull_targets();
  
  msg(sprintf('Pulling from %u remote targets.', count($targets)));
  
  //No targets, means no batch.
  if(!count($targets))
    return array();
  
  //Pull a list of URL's from each target.
  $urls = array();
  foreach($targets as $target){
    
    //First pull, or an update?
    if(!$target['dt_last_pull'])
      $url = $target['base_url'].'/sync/pull/all';
    else
      $url = $target['base_url'].'/sync/pull/since/'.intval($target['dt_last_pull']);
    
    //Go for it :D
    $target['pull_data'] = json_decode(fetch_url($url), true);
    
    //If we didn't get any JSON.
    if($target['pull_data'] === null){
      msg(sprintf('Failed to pull from "%s".', $url));
      continue;
    }
    
    //Add all entries as keys, to remove duplicates.
    foreach($target['pull_data']['results'] as $url)
      $urls[$url]=true;
    
  }
  
  //Now that we have our URL's. Store them in the queue.
  foreach($urls as $url=>$bool){
    if($url) sync_pull($url);
  }
  
  //Since this all worked out, mark each source with the timestamp of pulling.
  foreach($targets as $target){
    if($targets['pull_data'] && $targets['pull_data']['now'])
      q("UPDATE `sync-targets` SET `dt_last_pull`=%u WHERE `base_url`='%s'", $targets['pull_data']['now'], dbesc($targets['base_url']));
  }
  
  //Finally, return a batch of this.
  return get_queued_pull_batch($a);
  
}

/**
 * Gathers an array of URL's to scrape from the pulling targets.
 * @param  object $a The App instance.
 * @return array URL's to scrape.
 */
function get_pulling_job($a)
{
  
  //No pulling today...
  if(!$a->config['syncing']['enable_pulling'])
    return array();
  
  //Firstly, finish the items from our queue.
  $batch = get_queued_pull_batch($a);
  if(count($batch)) return $batch;
  
  //If that is empty, fill the queue with remote items and return a batch of that.
  $batch = get_remote_pull_batch($a);
  if(count($batch)) return $batch;
  
}

/**
 * For a single fork during the pull jobs.
 * Takes a lower priority and pulls a batch of items.
 * @param  int    $i          The index number of this worker (for round-robin).
 * @param  int    $threadc    The amount of workers (for round-robin).
 * @param  array  $pull_batch A batch of URL's to pull.
 * @param  string $db_host    DB host to connect to.
 * @param  string $db_user    DB user to connect with.
 * @param  string $db_pass    DB pass to connect with.
 * @param  mixed  $db_data    Nobody knows.
 * @param  mixed  $install    Maybe a boolean.
 * @return void
 */
function pull_worker($i, $threadc, $pull_batch, $db_host, $db_user, $db_pass, $db_data, $install)
{
  
  //Lets be nice, we're only doing maintenance here...
  pcntl_setpriority(5);
  
  //Get personal DBA's.
  global $db;
  $db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
  
  //Get our (round-robin) workload from the batch.
  $workload = array();
  while(isset($pull_batch[$i])){
    $entry = $pull_batch[$i];
    $workload[] = $entry;
    $i+=$threadc;
  }
  
  //While we've got work to do.
  while(count($workload)){
    $entry = array_pop($workload);
    set_time_limit(20); //This should work for 1 submit.
    msg("Submitting ".$entry['url']);
    run_submit($entry['url']);
  }
  
}

/**
 * Runs a pulling job, creating several threads to do so.
 * @param  object $a The App instance.
 * @param  array  $pull_batch A batch of URL's to pull.
 * @param  string $db_host    DB host to connect to.
 * @param  string $db_user    DB user to connect with.
 * @param  string $db_pass    DB pass to connect with.
 * @param  mixed  $db_data    Nobody knows.
 * @param  mixed  $install    Maybe a boolean.
 * @return void
 */
function run_pulling_job($a, $pull_batch, $db_host, $db_user, $db_pass, $db_data, $install)
{
  
  //We need the scraper.
  require_once('include/submit.php');
  
  //POSIX threads only.
  if(!function_exists('pcntl_fork')){
    msg('Error: no pcntl_fork support. Are you running a different OS? Report an issue please.', true);
  }
  
  //Create the threads we need.
  $items = count($pull_batch);
  $threadc = min($a->config['syncing']['pulling_threads'], $items); //Don't need more threads than items.
  $threads = array();
  
  msg("Creating $threadc pulling threads for $items profiles.");
  
  //Build the threads.
  for($i = 0; $i < $threadc; $i++){
    
    $pid = pcntl_fork();
    if($pid === -1) msg('Error: something went wrong with the fork. '.pcntl_strerror(), true);
    
    //You're a child, go do some labor!
    if($pid === 0){pull_worker($i, $threadc, $pull_batch, $db_host, $db_user, $db_pass, $db_data, $install); exit;}
    
    //Store the list of PID's.
    if($pid > 0) $threads[] = $pid;
    
  }
  
  //Wait for all child processes.
  $theading_problems = false;
  foreach($threads as $pid){
    pcntl_waitpid($pid, $status);
    if($status !== 0){
      $theading_problems = true;
      msg("Bad process return value $pid:$status");
    }
  }
  
  //If we did not have any "threading" problems.
  if(!$theading_problems){
    
    //Reconnect
    global $db;
    $db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
    
    //Create a query for deleting this queue.
    $where = array();
    foreach($pull_batch as $item) $where[] = dbesc($item['url']);
    $where = "WHERE `url` IN ('".implode("', '", $where)."')";
    
    //Remove the items from queue.
    q("DELETE FROM `sync-pull-queue` $where LIMIT %u", count($pull_batch));
    msg('Removed items from pull queue.');
    
  }
  
}