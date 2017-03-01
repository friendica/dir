<?php

if(! function_exists('home_init')) {
function home_init(&$a) {

	$r = q("SELECT * FROM `user` WHERE 1 LIMIT 1");
	if(count($r))
		goaway( $a->get_baseurl() . "/profile/" . $r[0]['nickname'] );
	else
		goaway( $a->get_baseurl() . "/register" );

}}


