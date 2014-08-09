<?php

require_once('include/submit.php');
require_once('include/sync.php');

function submit_content(&$a) {
	
  //Decode the URL.
	$url = hex2bin(notags(trim($_GET['url'])));
  
  //Currently we simply push RAW URL's to our targets.
  sync_push($url);
  
  //Run the submit sequence.
	run_submit($url);
	exit;

}