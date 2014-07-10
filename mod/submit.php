<?php

require_once('include/submit.php');

function submit_content(&$a) {
	
	$url = hex2bin(notags(trim($_GET['url'])));
	run_submit($url);
	exit;

}