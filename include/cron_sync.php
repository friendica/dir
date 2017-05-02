<?php

/*

  #TODO:

 * First do the pulls then the pushes.
  If pull prevents the push, the push queue just creates a backlog until it gets a chance to push.

 * When doing a first-pull, there's a safety mechanism for the timeout and detecting duplicate attempts.

  1. Perform all JSON pulls on the source servers.
  2. Combine the results into one giant pool of URLs.
  3. Write this pool to a file (TODO-file).
  4. Shuffle the pool in RAM.
  5. Start threads for crawling.
  6. Every finished crawl attempt (successful or not) should write to a 2nd file (DONE-file).

  IF the first-pull times out, don't do anything else.
  Otherwise, mark the dates we last performed a pull from each server.

 * When resuming a first-pull.

  1. Check for the TODO-file and the DONE-file.
  2. Remove the entries in the DONE-file from the pool in the TODO-file.
  3. Replace the TODO-file with the updated pool.
  4. Perform steps 4, 5 and 6 (shuffle, create threads and crawl) from before.

  This way you can resume without repeating attempts.

 * Write documentation about syncing.

 * Create "official" directory policy for my directory.

 * Decide if a retry mechanism is desirable for pulling (for the failed attempts).
  After all, you did imply trust when you indicated to pull from that source...
  This could be done easily by doing a /sync/pull/all again from those sources.

 * Decide if cron_sync.php should be split into push pull and pull-all commands.

 */

//Startup.
require_once 'boot.php';

use Friendica\Directory\App;

// Debug stuff.
ini_set('display_errors', 1);
ini_set('log_errors', '0');
error_reporting(E_ALL ^ E_NOTICE);

$start_syncing = time();

$a = new App;

//Create a simple log function for CLI use.
global $verbose;
$verbose = $argv[1] === 'verbose';

function msg($message, $fatal = false)
{
	global $verbose;
	if ($verbose || $fatal)
		echo($message . PHP_EOL);
	logger($message);
	if ($fatal) {
		exit(1);
	}
}

//Config.
require_once '.htconfig.php';

//Connect the DB.
require_once  'dba.php';

$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);

//Import syncing functions.
require_once 'sync.php';

//Get work for pulling.
$pull_batch = get_pulling_job($a);

//Get work for pushing.
list($push_targets, $push_batch) = get_pushing_job($a);

//Close the connection for now. Process forking and DB connections are not the best of friends.
$db->getdb()->close();

if (count($pull_batch)) {
	run_pulling_job($a, $pull_batch, $db_host, $db_user, $db_pass, $db_data, $install);
}

//Do our multi-fork job, if we have a batch and targets.
if (count($push_targets) && count($push_batch)) {
	run_pushing_job($push_targets, $push_batch, $db_host, $db_user, $db_pass, $db_data, $install);
}

//Log the time it took.
$time = time() - $start_syncing;
msg("Syncing completed. Took $time seconds.");
