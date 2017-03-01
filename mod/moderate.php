<?php


function moderate_post(&$a) {

	if(! $_SESSION['uid'])
		return;

	$id = intval($_POST['id']);
	if(! $id)
		return;

	$action = $_POST['action'];

	if($action == 'bogus') {
		q("DELETE FROM `flag` WHERE `pid` = %d",
			intval($id)
		);
		goaway($a->get_baseurl() . '/admin');
	}

	if($action != 'censor' && $action != 'dead')
		return;

	$r = q("SELECT * FROM `profile` WHERE `id` = %d LIMIT 1",
		intval($id)
	);

	if(! count($r))
		return;

	if($action == 'censor') {
		q("UPDATE `profile` SET `censored` = 1 WHERE `id` = %d LIMIT 1",
			intval($id)
		);
		q("DELETE FROM `flag` WHERE `pid` = %d",
			intval($id)
		);
		notice('Profile censored<br />');
	}

	if($action == 'dead') {
		q("DELETE FROM `profile` WHERE `id` = %d LIMIT 1",
			intval($id)
		);
		q("DELETE FROM `photo` WHERE `profile-id` = %d LIMIT 1",
			intval($id)
		);
		q("DELETE FROM `flag` WHERE `pid` = %d",
			intval($id)
		);
		notice('Dead profile removed<br />');
	}

	goaway($a->get_baseurl() . '/admin');
}



function moderate_content(&$a) {

	if(! $_SESSION['uid']) {
		notice("Permission denied.");
		return;
	}

	if($a->argc > 1)
		$id = intval($a->argv[1]);
	if($a->argc > 2)
		$reason = $a->argv[2];
	
	if($id) {
		$r = q("SELECT * FROM `profile` WHERE `id` = %d LIMIT 1",
			intval($id)
		);
		if(! count($r)) {
			notice("Entry does not exist.");
			q("DELETE FROM `flag` WHERE `pid` = %d",
				intval($id)
			);
			goaway($a->get_baseurl() . '/admin');
		}
	}else{
		goaway($a->get_baseurl() . '/admin');
	}
	
	$c .= "<h1>Moderate/delete profile</h1>";


		$tpl = file_get_contents('view/directory_item.tpl');

		foreach($r as $rr) {

			$pdesc = (($rr['pdesc']) ? $rr['pdesc'] . '<br />' : '');

			$details = '';
			if(strlen($rr['locality']))
				$details .= $rr['locality'];
			if(strlen($rr['region'])) {
				if(strlen($rr['locality']))
					$details .= ', ';
				$details .= $rr['region'];
			}
			if(strlen($rr['country-name'])) {
				if(strlen($details))
					$details .= ', ';
				$details .= $rr['country-name'];
			}

			if(strlen($rr['gender']))
				$details .= '<br />' . t('Gender: ') . t($rr['gender']) ;

			$o .= replace_macros($tpl,array(
				'$id' => $rr['id'],
				'$mod' => '',
				'$profile-link' => $rr['homepage'],
				'$photo' => (($rr['photo']) ? $rr['photo'] : $a->get_baseurl() . '/photo/' . $rr['id']),
				'$alt-text' => $rr['name'] . ' ' . '(' . $rr['id'] . ')',
				'$name' => $rr['name'],
				'$star' => '',
				'$pclass' => (($rr['comm']) ? ' group' : ''),
				'$pgroup' => (($rr['comm']) ? '<div class="directory-group">' . t('[Public Group]') . '</div>' : ''),
				'$details' => $pdesc . $details,
				'$marital' => ((strlen($rr['marital'])) ? '<div class="marital"><span class="marital-label"><span class="heart">&hearts;</span> Status: </span><span class="marital-text">' . $rr['marital'] . '</span></div>' : '')
  


			));

		}

		$o .= "<div class=\"directory-end\" ></div>\r\n";

	$c .= '<br /><br /><iframe height="400" width="800" src="' . $rr['homepage'] . '" class="profile-moderate-preview"></iframe>';
	$c .= '<br />' . $rr['homepage'] . '<br />';

	$o .= '<form action="moderate" method="post" >';
	$checked = (($reason === '1') ? 'checked="checked" ' : ''); 
	$o .= '<label><input type="radio" name="action" value="censor"' . $checked . '>Censor Profile</label><br /><br />';
	$checked = (($reason === '2') ? 'checked="checked" ' : ''); 
	$o .= '<label><input type="radio" name="action" value="dead"' . $checked . '" >Dead Account</label><br /><br />';
 
	$o .= '<label><input type="radio" name="action" value="bogus" >Bogus request</label><br /><br />';


	$o .= '<input type="hidden" name="id" value="' . $id . '" ><br /><br />';
	$o .= '<input type="submit" name="submit" value="submit"><br />';
	$o .= '</form>';

$a->page['aside'] = $o;

	return $c;

}