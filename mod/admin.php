<?php


function admin_content(&$a) {
	if(! $_SESSION['uid']) {
		notice("Permission denied.");
		return;
	}

	$r = q("SELECT COUNT(*) FROM `flag` as `ftotal` WHERE 1");
	if(count($r))
		$a->set_pager_total($r[0]['ftotal']);

	$r = q("SELECT * FROM `flag` WHERE 1 ORDER BY `total` DESC LIMIT %d, %d ",
		intval($a->pager['start']),
		intval($a->pager['itemspage'])
	);

	if(! count($r)) {
		notice("No entries.");
		return;
	}

	if(count($r)) {
		foreach($r as $rr) {
			if($rr['reason'] == 1)
				$str = 'censor';
			if($rr['reason'] == 2)
				$str = 'dead'; 
			$o .=  '<a href="' . 'moderate/' . $rr['pid'] . '/' . $str . '">'
				. $str . ' profile: ' . $rr['pid'] . ' (' . $rr['total'] . ')</a><br />';
		}
	}

	$o .= paginate($a);
	return $o;
}