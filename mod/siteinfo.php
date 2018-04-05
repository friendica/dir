<?php

/*

site_name
version
url
addons (arr)
register_policy
admin:
	name
	profile

*/


function siteinfo_content(&$a) {

	$o .= '<style>tr { margin-top: 10px; td { margin: 0px 5px 0px 5px; } </style>';

	$o .= '<h3>Friendica Public Portals</h3>';

	$o .= '<p>Sites running Friendica that you can join. (Please check current registration status.)</p>';


	$o .= '<p>Friendica is experiencing very rapid growth and we need more public portals - as some of our primary servers are reaching capacity. Friendica is a decentralised and distributed network. Help us share the load. If you can provide a Friendica server for public use, please send the URL to info at friendica dot com. We will include you in our list.</p>';


	$r = q("select * from site where url != '' and version != '' and not addons like '%%testdrive%%' order by rand()");

	$policy = array ( 'REGISTER_CLOSED' => 'closed', 'REGISTER_OPEN' => 'open', 'REGISTER_APPROVE' => 'requires approval');

	$results = false;

	if(count($r)) {
		$results = true;
		$o .= '<table border=1>';
			$o .= '<tr>';
			$o .= '<td>' . t('Site Name') . '</td>';
			$o .= '<td>' . t('Registration') . '</td>';
			$o .= '<td>' . t('Additional Info') . '</td>';
			$o .= '<td>' . t('Version') . '</td>';
			$o .= '<td>' . t('Addons Installed') . '</td>';
			$o .= '<td>' . t('Site Administrator') . '</td>';
			$o .= '<td>' . t('Record Updated (UTC)') . '</td>';
			$o .= '</tr>';

		foreach($r as $rr) {
			if(! $rr['version'])
				continue;
			$o .= '<tr>';
			$o .= '<td>' . '<a href="' . $rr['url'] . '"><strong>' . $rr['name'] . '</strong><br />' . $rr['url'] . '</a>' . '</td>';
			$o .= '<td>' . $policy[$rr['reg_policy']] . '</td>';
			$o .= '<td>' . $rr['info'] . '</td>';
			$o .= '<td>' . $rr['version'] . '</td>';
			$o .= '<td>' . str_replace(',',', ',$rr['addons']) . '</td>';
			$o .= '<td>' . '<a href="' . $rr['admin_profile'] . '">' . $rr['admin_name'] . '</a>' . '</td>';
			$o .= '<td>' . $rr['updated'] . '</td>';
			$o .= '</tr>';
		}
	}

	$r = q("select * from site where url != '' and version != '' and addons like '%%testdrive%%' order by rand()");

	if(count($r)) {
		$o .= '<tr><td colspan="7" height="100px" ><strong>-- Demo and test sites -- Limited account duration with expiration --</stron></td></tr>';
		foreach($r as $rr) {
			if(! $rr['version'])
				continue;
			$o .= '<tr>';
			$o .= '<td>' . '<a href="' . $rr['url'] . '"><strong>' . $rr['name'] . '</strong><br />' . $rr['url'] . '</a>' . '</td>';
			$o .= '<td>' . $policy[$rr['reg_policy']] . '</td>';
			$o .= '<td>' . $rr['info'] . '</td>';
			$o .= '<td>' . $rr['version'] . '</td>';
			$o .= '<td>' . str_replace(',',', ',$rr['addons']) . '</td>';
			$o .= '<td>' . '<a href="' . $rr['admin_profile'] . '">' . $rr['admin_name'] . '</a>' . '</td>';
			$o .= '<td>' . $rr['updated'] . '</td>';
			$o .= '</tr>';
		}
	}

	if($results)
		$o .= '</table>';

//	$r = q("select * from site where url != '' and version = '' order by name asc");
//	if(count($r)) {
//		$o .= '<p>Sites that could not be verified. May be running older versions.</p>';
//		foreach($r as $rr) {
//				$o .= '<a href="' . $rr['url'] . '">' . $rr['url'] . '</a><br />';
//		}
//	}




	return $o;
}
