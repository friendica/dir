<?php

require_once('library/HTML5/Parser.php');

if(! function_exists('attribute_contains')) {
function attribute_contains($attr,$s) {
	$a = explode(' ', $attr);
	if(count($a) && in_array($s,$a))
		return true;
	return false;
}}


if(! function_exists('noscrape_dfrn')) {
function noscrape_dfrn($url) {
	$submit_noscrape_start = microtime(true);
	$data = fetch_url($url);
	$submit_noscrape_request_end = microtime(true);
	if(empty($data)) return false;
	$parms = json_decode($data, true);
	if(!$parms || !count($parms)) return false;
	$parms['tags'] = implode(' ', (array)$parms['tags']);
	$submit_noscrape_end = microtime(true);
	$parms['_timings'] = array(
		'fetch' => round(($submit_noscrape_request_end - $submit_noscrape_start) * 1000),
		'scrape' => round(($submit_noscrape_end - $submit_noscrape_request_end) * 1000)
	);
	return $parms;
}}

if(! function_exists('scrape_dfrn')) {
function scrape_dfrn($url, $max_nodes=3500) {
	
	$minNodes = 100; //Lets do at least 100 nodes per type.
	$timeout = 10; //Timeout will affect batch processing.
	
	//Try and cheat our way into faster profiles.
	if(strpos($url, 'tab=profile') === false){
		$url .= (strpos($url, '?') > 0 ? '&' : '?').'tab=profile';
	}
	
	$scrape_start = microtime(true);
	
	$ret = array();
	$s = fetch_url($url, $timeout);
	
	$scrape_fetch_end = microtime(true);
	
	if(! $s) 
		return $ret;
	
	$dom = HTML5_Parser::parse($s);
	
	if(! $dom)
		return $ret;
	
	$items = $dom->getElementsByTagName('meta');
	
	// get DFRN link elements
	$nodes_left = max(intval($max_nodes), $minNodes);
	$targets = array('hide', 'comm', 'tags');
	$targets_left = count($targets);
	foreach($items as $item) {
		$x = $item->getAttribute('name');
		if($x == 'dfrn-global-visibility') {
			$z = strtolower(trim($item->getAttribute('content')));
			if($z != 'true')
				$ret['hide'] = 1;
			if($z === 'false')
				$ret['explicit-hide'] = 1;
			$targets_left = pop_scrape_target($targets, 'hide');
		}
		if($x == 'friendika.community' || $x == 'friendica.community') {
			$z = strtolower(trim($item->getAttribute('content')));
			if($z == 'true')
				$ret['comm'] = 1;
			$targets_left = pop_scrape_target($targets, 'comm');
		}
		if($x == 'keywords') {
			$z = str_replace(',',' ',strtolower(trim($item->getAttribute('content'))));
			if(strlen($z))
				$ret['tags'] = $z;
			$targets_left = pop_scrape_target($targets, 'tags');
		}
		$nodes_left--;
		if($nodes_left <= 0 || $targets_left <= 0) break;
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
	$targets = array('fn', 'pdesc', 'photo', 'key', 'locality', 'region', 'postal-code', 'country-name');
	$targets_left = count($targets);
	foreach($items as $item) {
		if(attribute_contains($item->getAttribute('class'), 'vcard')) {
			$level2 = $item->getElementsByTagName('*');
			foreach($level2 as $x) {
				if(attribute_contains($x->getAttribute('class'),'fn')){
					$ret['fn'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'fn');
				}
				if(attribute_contains($x->getAttribute('class'),'title')){
					$ret['pdesc'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'pdesc');
				}
				if(attribute_contains($x->getAttribute('class'),'photo')){
					$ret['photo'] = $x->getAttribute('src');
					$targets_left = pop_scrape_target($targets, 'photo');
				}
				if(attribute_contains($x->getAttribute('class'),'key')){
					$ret['key'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'key');
				}
				if(attribute_contains($x->getAttribute('class'),'locality')){
					$ret['locality'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'locality');
				}
				if(attribute_contains($x->getAttribute('class'),'region')){
					$ret['region'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'region');
				}
				if(attribute_contains($x->getAttribute('class'),'postal-code')){
					$ret['postal-code'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'postal-code');
				}
				if(attribute_contains($x->getAttribute('class'),'country-name')){
					$ret['country-name'] = $x->textContent;
					$targets_left = pop_scrape_target($targets, 'country-name');
				}
      }
		}
		$nodes_left--;
		if($nodes_left <= 0 || $targets_left <= 0) break;
	}
	
	$scrape_end = microtime(true);
	$fetch_time = round(($scrape_fetch_end - $scrape_start) * 1000);
	$scrape_time = round(($scrape_end - $scrape_fetch_end) * 1000);
	
	$ret['_timings'] = array(
		'fetch' => $fetch_time,
		'scrape' => $scrape_time
	);
	
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

if(! function_exists('pop_scrape_target')) {
function pop_scrape_target(&$array, $name) {
	$at = array_search($name, $array);
	unset($array[$at]);
	return count($array);
}}

