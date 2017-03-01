<?php

function login_init(&$a) {
	if(local_user()) {
		notice("Logged in");
		goaway($a->get_baseurl());
	}
}


function login_content(&$a) {
	return login(false);
}