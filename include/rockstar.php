<?php

	require_once("boot.php");

	$a = new App;

	@include(".htconfig.php");
	require_once("dba.php");
	$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);
		unset($db_host, $db_user, $db_pass, $db_data);

	require_once("session.php");
	require_once("datetime.php");

	$a->set_baseurl(get_config('system','url'));

	$u = q("SELECT * FROM `user` WHERE 1 LIMIT 1");
	if(! count($u))
		killme();

	$uid = $u[0]['uid'];
	$nickname = $u[0]['nickname'];

	$intros = q("SELECT `intro`.*, `intro`.`id` AS `intro_id`, `contact`.* 
		FROM `intro` LEFT JOIN `contact` ON `contact`.`id` = `intro`.`contact-id`
		WHERE `intro`.`blocked` = 0 AND `intro`.`ignore` = 0");

	if(! count($intros))
		return;


	foreach($intros as $intro) {
	
		$intro_id      = intval($intro['intro_id']);

		$dfrn_id       = $intro['issued-id'];
		$contact_id    = $intro['contact-id'];
		$relation      = $intro['rel'];
		$site_pubkey   = $intro['site-pubkey'];
		$dfrn_confirm  = $intro['confirm'];
		$aes_allow     = $intro['aes_allow'];

		$res=openssl_pkey_new(array(
        		'digest_alg' => 'whirlpool',
        		'private_key_bits' => 4096,
			'encrypt_key' => false ));

		$private_key = '';

		openssl_pkey_export($res, $private_key);

		$pubkey = openssl_pkey_get_details($res);
		$public_key = $pubkey["key"];

		$r = q("UPDATE `contact` SET `issued-pubkey` = '%s', `prvkey` = '%s' WHERE `id` = %d LIMIT 1",
			dbesc($public_key),
			dbesc($private_key),
			intval($contact_id)
		);

		$params = array();

		$src_aes_key = random_string();
		$result = "";

		openssl_private_encrypt($dfrn_id,$result,$u[0]['prvkey']);

		$params['dfrn_id'] = $result;
		$params['public_key'] = $public_key;

		$my_url = $a->get_baseurl() . '/profile/' . $nickname ;

		openssl_public_encrypt($my_url, $params['source_url'], $site_pubkey);

		if($aes_allow && function_exists('openssl_encrypt')) {
			openssl_public_encrypt($src_aes_key, $params['aes_key'], $site_pubkey);
			$params['public_key'] = openssl_encrypt($public_key,'AES-256-CBC',$src_aes_key);
		}

		$res = post_url($dfrn_confirm,$params);

		$xml = simplexml_load_string($res);
		$status = (int) $xml->status;
		switch($status) {
			case 0:
				break;
			case 1:
				// birthday paradox - generate new dfrn-id and fall through.

				$new_dfrn_id = random_string();
				$r = q("UPDATE contact SET `issued-id` = '%s' WHERE `id` = %d LIMIT 1",
					dbesc($new_dfrn_id),
					intval($contact_id)
				);
			case 2:
				break;

			case 3:
			default:
				break;
		}

		if(($status == 0 || $status == 3) && ($intro_id)) {

			// delete the notification

			$r = q("DELETE FROM `intro` WHERE `id` = %d LIMIT 1",
				intval($intro_id)
			);
		}
		if($status != 0) 
			killme();
		
		require_once("Photo.php");

		$photo_failure = false;


		$filename = basename($intro['photo']);
		$img_str = fetch_url($intro['photo'],true);
		$img = new Photo($img_str);
		if($img) {

			$img->scaleImageSquare(175);		
			$hash = hash('md5',uniqid(mt_rand(),true));

			$r = $img->store($contact_id, $hash, $filename, t('Contact Photos'), 4 );

			if($r === false)
				$photo_failure = true;
			$img->scaleImage(80);

			$r = $img->store($contact_id, $hash, $filename, t('Contact Photos'), 5 );

			if($r === false)
				$photo_failure = true;

			$photo = $a->get_baseurl() . '/photo/' . $hash . '-4.jpg';
			$thumb = $a->get_baseurl() . '/photo/' . $hash . '-5.jpg';
		}
		else
			$photo_failure = true;

		if($photo_failure) {
			$photo = $a->get_baseurl() . '/images/default-profile.jpg';
			$thumb = $a->get_baseurl() . '/images/default-profile-sm.jpg';
		}

		$r = q("UPDATE `contact` SET `photo` = '%s', `thumb` = '%s', `rel` = %d, 
			`name-date` = '%s', `uri-date` = '%s', `avatar-date` = '%s', 
			`readonly` = %d, `profile-id` = %d, `blocked` = 0, `pending` = 0, 
			`network` = 'dfrn' WHERE `id` = %d LIMIT 1",
			dbesc($photo),
			dbesc($thumb),
			intval(($relation == DIRECTION_OUT) ? DIRECTION_BOTH : DIRECTION_IN),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			intval((x($a->config,'rockstar-readonly')) ? $a->config['rockstar-readonly'] : 0),
			intval((x($a->config,'rockstar-profile'))  ? $a->config['rockstar-profile'] : 0), 
			intval($contact_id)
		);

	}
	killme();

