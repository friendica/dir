<?php

function versions_content(&$a){
	
	$sites = array();
	
	//Grab a version list.
	$versions = '';
	$r = q("SELECT count(*) as `count`, `version` FROM `site-health` WHERE `version` IS NOT NULL GROUP BY `version` ORDER BY `version` DESC");
	if(count($r)){
		foreach($r as $version){
			$versions .=
				($version['count'] >= 10 ? '<b>' : '').
					$version['version'] . ' ('.$version['count'].')<br>'."\r\n".
				($version['count'] >= 10 ? '</b>' : '');
		}
	}
	
	$tpl .= file_get_contents('view/versions.tpl');
	return replace_macros($tpl, array(
		'$versions' => $versions
	));
	
}