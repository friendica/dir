<?php


function gender_selector($current="",$suffix="") {
	$select = array('', t('Male'), t('Female'), t('Transsexual'), t('Hermaphrodite'), t('Neuter'), t('Other'), t('Undecided'));

	$o .= "<select name=\"gender$suffix\" id=\"gender-select$suffix\" size=\"1\" >";
	foreach($select as $selection) {
		$selected = (($selection == $current) ? ' selected="selected" ' : '');
		$o .= "<option value=\"$selection\" $selected >$selection</option>";
	}
	$o .= '</select>';
	return $o;
}	

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


function marital_selector($current="",$suffix="") {
	$select = array('', t('Single'), t('Lonely'), t('Available'), t('Unavailable'), t('Dating'), t('Unfaithful'), t('Sex Addict'), t('Friends'), t('Friends/Benefits'), t('Casual'), t('Engaged'), t('Married'), t('Partners'), t('Cohabiting'), t('Happy'), t('Not Looking'), t('Swinger'), t('Betrayed'), t('Separated'), t('Unstable'), t('Divorced'), t('Widowed'), t('Uncertain'), t('Complicated'), t('Don\'t care'), t('Ask me') );

	$o .= "<select name=\"marital[]\" id=\"marital-select\" multiple=\"multiple\" size=\"2\" >";
	foreach($select as $selection) {
		$selected = (($selection == $current) ? ' selected="selected" ' : '');
		$o .= "<option value=\"$selection\" $selected >$selection</option>";
	}
	$o .= '</select>';
	return $o;
}	
