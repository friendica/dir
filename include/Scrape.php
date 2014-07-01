<?php

require_once('library/HTML5/Parser.php');

if(! function_exists('attribute_contains')) {
function attribute_contains($attr,$s) {
	$a = explode(' ', $attr);
	if(count($a) && in_array($s,$a))
		return true;
	return false;
}}


if(! function_exists('scrape_dfrn')) {
function scrape_dfrn($url, $max_nodes=5000) {
	
	$minNodes = 100; //Lets do at least 100 nodes per type.
	$timeout = 10; //Timeout will affect batch processing.
	
	$ret = array();
	$s = fetch_url($url, $timeout);

	if(! $s) 
		return $ret;

	$dom = HTML5_Parser::parse($s);

	if(! $dom)
		return $ret;


	$items = $dom->getElementsByTagName('meta');

	// get DFRN link elements
	$nodes_left = max(intval($max_nodes), $minNodes);
	foreach($items as $item) {
		$x = $item->getAttribute('name');
		if($x == 'dfrn-global-visibility') {
			$z = strtolower(trim($item->getAttribute('content')));
			if($z != 'true')
				$ret['hide'] = 1;
		}
		if($x == 'friendika.community' || $x == 'friendica.community') {
			$z = strtolower(trim($item->getAttribute('content')));
			if($z == 'true')
				$ret['comm'] = 1;
		}
		if($x == 'keywords') {
			$z = str_replace(',',' ',strtolower(trim($item->getAttribute('content'))));
			if(strlen($z))
				$ret['tags'] = $z;
		}
		$nodes_left--;
		if($nodes_left <= 0) break;
	}

	$items = $dom->getElementsByTagName('link');

	// get DFRN link elements
	
	$nodes_left = max(intval($max_nodes), $minNodes);
	foreach($items as $item) {
		$x = $item->getAttribute('rel');
		if(substr($x,0,5) == "dfrn-")
			$ret[$x] = $item->getAttribute('href');
		$nodes_left--;
		if($nodes_left <= 0) break;
	}

	// Pull out hCard profile elements
	
	$nodes_left = max(intval($max_nodes), $minNodes);
	$items = $dom->getElementsByTagName('*');
	foreach($items as $item) {
		if(attribute_contains($item->getAttribute('class'), 'vcard')) {
			$level2 = $item->getElementsByTagName('*');
			foreach($level2 as $x) {
				if(attribute_contains($x->getAttribute('class'),'fn'))
					$ret['fn'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'title'))
					$ret['pdesc'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'photo'))
					$ret['photo'] = $x->getAttribute('src');
				if(attribute_contains($x->getAttribute('class'),'key'))
					$ret['key'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'locality'))
					$ret['locality'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'region'))
					$ret['region'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'postal-code'))
					$ret['postal-code'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'country-name'))
					$ret['country-name'] = $x->textContent;
				if(attribute_contains($x->getAttribute('class'),'x-gender'))
					$ret['gender'] = $x->textContent;

        		}
		}
		if(attribute_contains($item->getAttribute('class'),'marital-text'))
			$ret['marital'] = $item->textContent;
		$nodes_left--;
		if($nodes_left <= 0) break;
	}
	return $ret;
}}


if(! function_exists('validate_dfrn')) {
function validate_dfrn($a) {
	$errors = 0;
	if(! x($a,'key'))
		$errors ++;
	if(! x($a,'dfrn-request'))
		$errors ++;
	if(! x($a,'dfrn-confirm'))
		$errors ++;
	if(! x($a,'dfrn-notify'))
		$errors ++;
	if(! x($a,'dfrn-poll'))
		$errors ++;
	return $errors;
}}

