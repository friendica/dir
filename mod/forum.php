<?php

function forum_init(&$a) {
	header("location: " . $a->get_baseurl() . '/directory/forum');
	exit;
}