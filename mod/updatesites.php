<?php

require_once('include/datetime.php');

function updatesites_content(&$a) {


	$r = q("select * from site where url != '' order by name asc");

	if(count($r)) {
		foreach($r as $rr) {
			$s = '';
			$s = fetch_url($rr['url'] . '/friendica/json');
			if($s)
				$j = json_decode($s);
			else
				continue;
			if($j) {
				$plugs = (array) $j->plugins;
				if(in_array('testdrive',$plugs)) {
					$j->site_name = '!!! Test/Demo ONLY. !!! ' . $j->site_name;
					$j->info = 'Accounts are temporary, expiration is enabled. ' . $j->info;
				}
				asort($plugs);

				q("UPDATE site set
					name = '%s',
					url = '%s', 
					version = '%s',
					plugins = '%s',
					reg_policy = '%s',
					info = '%s',
					admin_name = '%s',
					admin_profile = '%s',
					updated = '%s'
					where id = %d limit 1",
					dbesc($j->site_name),
					dbesc($j->url),
					dbesc($j->version),
					dbesc(implode(',',$plugs)),
					dbesc($j->register_policy),
					dbesc(($j->info) ? $j->info : ''),
					dbesc($j->admin->name),
					dbesc($j->admin->profile),
					dbesc(datetime_convert()),
					intval($rr['id'])
				);
			}
		}
	}
}
