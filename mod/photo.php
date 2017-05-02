<?php

use Friendica\Directory\App;

require_once 'datetime.php';

function photo_init(App $a)
{
	switch ($a->argc) {
		case 2:
			$photo = $a->argv[1];
			break;
		case 1:
		default:
			exit;
	}

	$profile_id = str_replace('.jpg', '', $photo);

	$r = q('SELECT * FROM `photo` WHERE `profile-id` = %d LIMIT 1', intval($profile_id));

	if (count($r)) {
		$data = $r[0]['data'];
	}

	if (x($data) === false || (!strlen($data))) {
		$data = file_get_contents('images/default-profile-sm.jpg');
	}

	//Enable async process from here.
	session_write_close();

	//Try and cache our result.
	$etag = md5($data);
	header('Etag: ' . $etag);
	header('Expires: ' . datetime_convert('UTC', 'UTC', 'now + 1 week', 'D, d M Y H:i:s' . ' GMT'));
	header('Cache-Control: max-age=' . intval(7 * 24 * 3600));

	if (function_exists('header_remove')) {
		header_remove('Pragma');
		header_remove('pragma');
	}

	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	header('Content-type: image/jpeg');
	echo $data;
	exit;
}
