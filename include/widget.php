<?php


function tags_widget() {
	$o = '';

	$r = q("select distinct(term), count(term) as total from tag group by term order by count(term) desc limit 10");
	if(count($r)) {
		$o .= '<div class="widget">';
		$o .= '<h3>' . t('Trending Interests') . '</h3>';
		$o .= '<ul>';
		foreach($r as $rr) {
			$o .= '<li><a href="directory?search=' . $rr['term'] . '" >' . $rr['term'] . '</a></li>';
		}
		$o .= '</ul></div>';
	}
	return $o;
}

function country_widget() {
	$o = '';

	$r = q("select distinct(`country-name`), count(`country-name`) as total from profile where `country-name` != '' group by `country-name` order by count(`country-name`) desc limit 10");
	if(count($r)) {
		$o .= '<div class="widget">';
		$o .= '<h3>' . t('Locations') . '</h3>';
		$o .= '<ul>';
		foreach($r as $rr) {
			$o .= '<li><a href="directory?search=' . $rr['country-name'] . '" >' . $rr['country-name'] . '</a></li>';
		}
		$o .= '</ul></div>';
	}
	return $o;
}
