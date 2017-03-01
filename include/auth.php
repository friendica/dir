<?php

// login/logout 

if((x($_SESSION,'authenticated')) && (! ($_POST['auth-params'] == 'login'))) {
	if($_POST['auth-params'] == 'logout' || $a->module == "logout") {
		unset($_SESSION['authenticated']);
		unset($_SESSION['uid']);
		unset($_SESSION['visitor_id']);
		unset($_SESSION['is_visitor']);
		unset($_SESSION['administrator']);
		unset($_SESSION['cid']);
		unset($_SESSION['theme']);
		notice( t('Logged out.') . EOL);
		goaway($a->get_baseurl());
	}
	if(x($_SESSION,'uid')) {
		$r = q("SELECT * FROM `user` WHERE `uid` = %d LIMIT 1",
			intval($_SESSION['uid']));
		if($r === NULL || (! count($r))) {
			goaway($a->get_baseurl());
		}
		$a->user = $r[0];
	}
}
else {

	unset($_SESSION['authenticated']);
	unset($_SESSION['uid']);
	unset($_SESSION['visitor_id']);
	unset($_SESSION['is_visitor']);
	unset($_SESSION['administrator']);
	unset($_SESSION['cid']);
	$encrypted = hash('whirlpool',trim($_POST['password']));

	if((x($_POST,'auth-params')) && $_POST['auth-params'] == 'login') {
		$r = q("SELECT * FROM `user` 
			WHERE `email` = '%s' AND `password` = '%s' LIMIT 1",
			dbesc(trim($_POST['login-name'])),
			dbesc($encrypted));

		if(($r === false) || (! count($r))) {
			notice( t('Login failed.') . EOL);
			goaway($a->get_baseurl());
  		}
		$_SESSION['uid'] = $r[0]['uid'];
		$_SESSION['theme'] = $r[0]['theme'];
		$_SESSION['authenticated'] = 1;
		$_SESSION['my_url'] = $a->get_baseurl() . '/profile/' . $r[0]['nickname'];

		notice( t("Welcome back ") . $r[0]['username'] . EOL);
		$a->user = $r[0];

	}
}

// Returns an array of group id's this contact is a member of.
// This array will only contain group id's related to the uid of this
// DFRN contact. They are *not* neccessarily unique across the entire site. 


if(! function_exists('init_groups_visitor')) {
function init_groups_visitor($contact_id) {
	$groups = array();
	$r = q("SELECT `gid` FROM `group_member` 
		WHERE `contact-id` = %d ",
		intval($contact_id)
	);
	if(count($r)) {
		foreach($r as $rr)
			$groups[] = $rr['gid'];
	}
	return $groups;
}}


