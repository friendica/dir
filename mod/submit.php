<?php

require_once('include/submit.php');

function submit_content(&$a) {
	
  //Decode the URL.
	$url = hex2bin(notags(trim($_GET['url'])));
  
  //Currently we simply push RAW URL's to our targets.
  //If we support it that is.
  if($a->config['syncing']['enable_pushing']){
    q("INSERT INTO `sync-queue` (`url`) VALUES ('%s')", dbesc($url));
  }
  
  //Run the submit sequence.
	run_submit($url);
	exit;

}