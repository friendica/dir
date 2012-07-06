<?php

function tags_init(&$a) {

	if($a->argc > 1)
		$limit = intval($a->argv[1]);

	require_once('widget.php');

	echo json_encode(get_taglist(($limit) ? $limit : 50));
	killme();
}
