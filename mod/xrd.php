<?php


function xrd_content(&$a) {

	$uri = notags(trim($_GET['uri']));
	$local = str_replace('acct:', '', $uri);
	$name = substr($local,0,strpos($local,'@'));

	$r = q("SELECT * FROM `user` WHERE `nickname` = '%s' LIMIT 1",
		dbesc($name)
	);
	if(! count($r))
		killme();

	$tpl = file_get_contents('view/xrd_person.tpl');

	$o = replace_macros($tpl, array(
		'$accturi' => $uri,
		'$profile_url' => $a->get_baseurl() . '/profile/' . $r[0]['nickname'],
		'$photo' => $a->get_baseurl() . '/photo/profile/1.jpg'
	));

	echo $o;
	killme();

}