<?php

require_once('include/submit.php');

function submit_content(&$a) {
	
	$url = hex2bin(notags(trim($_GET['url'])));
	run_submit($a, $url);
	exit;

}


function nuke_record($url) {

	$nurl = str_replace(array('https:','//www.'), array('http:','//'), $url);

	$r = q("SELECT `id` FROM `profile` WHERE ( `homepage` = '%s' OR `nurl` = '%s' ) ",
		dbesc($url),
		dbesc($nurl)
	);

	if(count($r)) {
		foreach($r as $rr) {
			q("DELETE FROM `photo` WHERE `profile-id` = %d LIMIT 1",
				intval($rr['id'])
			);
			q("DELETE FROM `profile` WHERE `id` = %d LIMIT 1",
				intval($rr['id'])
			);
		}
	}
	return;
}