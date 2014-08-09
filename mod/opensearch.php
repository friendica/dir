<?php

function opensearch_init(&$a) {

	$tpl = file_get_contents('view/osearch.tpl');
	header("Content-type: application/opensearchdescription+xml");
  echo replace_macros($tpl, array(
    '$base' => $a->get_baseurl()
  ));
	killme();
  
}