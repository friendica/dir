<?php


function sexpref_selector($current="",$suffix="") {
	$select = array('', t('Males'), t('Females'), t('Bisexual'), t('Autosexual'), t('Abstinent'), t('Virgin'), t('Nonsexual'));

	$o .= "<select name=\"sexual$suffix\" id=\"sexual-select$suffix\" size=\"1\" >";
	foreach($select as $selection) {
		$selected = (($selection == $current) ? ' selected="selected" ' : '');
		$o .= "<option value=\"$selection\" $selected >$selection</option>";
	}
	$o .= '</select>';
	return $o;
}		
