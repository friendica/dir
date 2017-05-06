<?php

function pubsites_init(&$a) {

	$ret = array();
	$r = q("select * from site where version != ''");
	foreach($r as $rr) {
		$entry = array('url' => $rr['url'], 'version' => $rr['version']);
		$ret[] = $entry;
	}

	echo json_encode(array('entries' => $ret));
	killme();
}
