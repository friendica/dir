<?php

require_once('include/datetime.php');

function submit_content(&$a) {

	$url = hex2bin(notags(trim($_GET['url'])));

	if(! strlen($url))
		exit;

	logger('Updating: ' . $url);

	$nurl = str_replace(array('https:','//www.'), array('http:','//'), $url);

	$profile_exists = false;

	$r = q("SELECT * FROM `profile` WHERE ( `homepage` = '%s' OR `nurl` = '%s' ) LIMIT 1",
		dbesc($url),
		dbesc($nurl)
	);

	if(count($r)) { 
		$profile_exists = true;
		$profile_id = $r[0]['id'];
	}

	require_once('Scrape.php');
	
	
	$parms = scrape_dfrn($url);
	

	if((! count($parms)) || (validate_dfrn($parms)))
		exit;

	if((x($parms,'hide')) || (! (x($parms,'fn')) && (x($parms,'photo')))) {
		if($profile_exists) {
			nuke_record($url);
		}
		return;
	}

	$photo = $parms['photo'];

	dbesc_array($parms);

	if(x($parms,'comm'))
		$parms['comm'] = intval($parms['comm']);

	if($profile_exists) {
		$r = q("UPDATE `profile` SET 
			`name` = '%s', 
			`pdesc` = '%s',
			`locality` = '%s', 
			`region` = '%s', 
			`postal-code` = '%s', 
			`country-name` = '%s', 
			`gender` = '%s', 
			`marital` = '%s', 
			`homepage` = '%s',
			`nurl` = '%s',
			`comm` = %d,
			`tags` = '%s',
			`updated` = '%s' 
			WHERE `id` = %d LIMIT 1",

			$parms['fn'],
			$parms['pdesc'],
			$parms['locality'],
			$parms['region'],
			$parms['postal-code'],
			$parms['country-name'],
			$parms['gender'],
			$parms['marital'],
			dbesc($url),
			dbesc($nurl),
			intval($parms['comm']),
			$parms['tags'],			
			dbesc(datetime_convert()),
			intval($profile_id)
		);
		logger('Update returns: ' . $r);

	}
	else {
		$r = q("INSERT INTO `profile` ( `name`, `pdesc`, `locality`, `region`, `postal-code`, `country-name`, `gender`, `marital`, `homepage`, `nurl`, `comm`, `tags`, `created`, `updated` )
			VALUES ( '%s', '%s', '%s', '%s' , '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s' )",
			$parms['fn'],
			$parms['pdesc'],
			$parms['locality'],
			$parms['region'],
			$parms['postal-code'],
			$parms['country-name'],
			$parms['gender'],
			$parms['marital'],
			dbesc($url),
			dbesc($nurl),
			intval($parms['comm']),
			$parms['tags'],
			dbesc(datetime_convert()),
			dbesc(datetime_convert())
		);
		logger('Insert returns: ' . $r);

		$r = q("SELECT `id` FROM `profile` WHERE `homepage` = '%s' LIMIT 1",
			dbesc($url)
		);
		if(count($r))
			$profile_id = $r[0]['id'];
	}

	if($parms['tags']) {
		$arr = explode(' ', $parms['tags']);
		if(count($arr)) {
			foreach($arr as $t) {
				$t = strip_tags(trim($t));
				$t = substr($t,0,254);

				if(strlen($t)) {
					$r = q("SELECT `id` FROM `tag` WHERE `term` = '%s' and `nurl` = '%s' LIMIT 1",
						dbesc($t),
						dbesc($nurl)
					);
					if(! count($r)) {
						$r = q("INSERT INTO `tag` (`term`, `nurl`) VALUES ('%s', '%s') ",
							dbesc($t),
							dbesc($nurl)
						);
					}
				}
			}
		}
	}

	require_once("Photo.php");

	$photo_failure = false;

	$img_str = fetch_url($photo,true);
	$img = new Photo($img_str);
	if($img) {
		$img->scaleImageSquare(80);
		$r = $img->store($profile_id);
	}
	if($profile_id) {
		$r = q("UPDATE `profile` SET `photo` = '%s' WHERE `id` = %d LIMIT 1",
			dbesc($a->get_baseurl() . '/photo/' . $profile_id . '.jpg'),
			intval($profile_id)
		);
	}
	else
		nuke_record($url);
	exit;

}


function nuke_record($url) {

	$nurl = str_replace(array('https:','//www.'), array('http:','//'), $url);

	$r = q("SELECT `id` FROM `profile` WHERE ( `homepage` = '%s' OR `nurl` = '%s' ) ",
		dbesc($url),
		dbesc($nurl)
	);

	if(count($r)) {
		foreach($r as $rr) {
			q("DELETE FROM `photo` WHERE `profile-id` = %d LIMIT 1",
				intval($rr['id'])
			);
			q("DELETE FROM `profile` WHERE `id` = %d LIMIT 1",
				intval($rr['id'])
			);
		}
	}
	return;
}