<?php

function opensearch_init(&$a) {

	$r = file_get_contents('view/osearch.tpl');
	header("Content-type: application/opensearchdescription+xml");

	echo $r;
	killme();
}