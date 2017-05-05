<?php

function tags_widget()
{
	$o = '';

	$r = q("SELECT `term`, COUNT(`term`) AS `total` FROM `tag` GROUP BY `term` ORDER BY COUNT(`term`) DESC LIMIT 20");
	if (count($r)) {
		$o .= '<div class="widget">';
		$o .= '<h3>' . t('Trending Interests') . '</h3>';
		$o .= '<ul>';
		foreach ($r as $rr) {
			$o .= '<li><a href="search?query=' . $rr['term'] . '" >' . $rr['term'] . '</a> (' . $rr['total'] . ')</li>';
		}
		$o .= '</ul></div>';
	}
	return $o;
}

function country_widget()
{
	$o = '';

	$r = q("SELECT `country-name`, COUNT(`country-name`) AS `total`"
		. " FROM `profile`"
		. " WHERE `country-name` != ''"
		. " GROUP BY `country-name`"
		. " ORDER BY COUNT(`country-name`) DESC"
		. " LIMIT 20");
	if (count($r)) {
		$o .= '<div class="widget">';
		$o .= '<h3>' . t('Locations') . '</h3>';
		$o .= '<ul>';
		foreach ($r as $rr) {
			$o .= '<li><a href="search?query=' . $rr['country-name'] . '" >' . $rr['country-name'] . '</a> (' . $rr['total'] . ')</li>';
		}
		$o .= '</ul></div>';
	}
	return $o;
}

function get_taglist($limit = 50)
{
	$r = q("SELECT DISTINCT(`term`), COUNT(`term`) AS `total` FROM `tag` GROUP BY `term` ORDER BY COUNT(`term`) DESC LIMIT %d",
		intval($limit)
	);

	return $r;
}
