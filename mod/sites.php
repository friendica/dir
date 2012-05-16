<?php

function sites_content(&$a) {


	$sites = array();

	$r = q("SELECT `nurl` FROM `profile` WHERE 1");
	if(count($r)) {
		foreach($r as $rr) {
			$h = parse_url($rr['nurl']);
			$host = $h['host'];
			if($h) {
				if(! isset($sites[$host]))
					$sites[$host] = 0;		
				$sites[$host] ++;
			}
		}
	}

	$total = 0;
	asort($sites);
	foreach($sites as $k => $v) {
		$o .= $k . ' (' . $v . ')' . "<br />\r\n";
		$total ++;
	}
	$o .= "Total: $total<br />\r\n";
	return $o;

}