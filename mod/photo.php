<?php

require_once('datetime.php');
function photo_init(&$a) {

	switch($a->argc) {
		case 2:
			$photo = $a->argv[1];
			break;
		case 1:
		default:
			exit;
	}

	$profile_id = str_replace('.jpg', '', $photo);

	$r = q("SELECT * FROM `photo` WHERE `profile-id` = %d LIMIT 1",
			intval($profile_id)
	);
	if(count($r)) {
		$data = $r[0]['data'];
	}
	if(x($data) === false || (! strlen($data))) {
		$data = file_get_contents('images/default-profile-sm.jpg'); 
	}

        header("Content-type: image/jpeg");
	header('Expires: ' . datetime_convert('UTC','UTC', 'now + 1 week', 'D, d M Y H:i:s' . ' GMT'));
	echo $data;
	exit;
}