<?php

require_once('include/site-health.php');

function health_content(&$a) {
	
	if($a->argc > 1){
		return health_details($a, $a->argv[1]);
	}
	
	if($_GET['s']){
		return health_search($a, $_GET['s']);
	}
	
	return health_summary($a);
	
}

function health_search(&$a, $search)
{
	
	if(strlen($search) < 3){
		$result = 'Please use at least 3 characters in your search';
	}
	
	else {
		
		$r = q("SELECT * FROM `site-health` WHERE `base_url` LIKE '%%%s%%' ORDER BY `health_score` DESC LIMIT 100", dbesc($search));
		if(count($r)){
			$result = '';
			foreach($r as $site){
				
				//Get user count.
				$site['users'] = 0;
				$r = q(
					"SELECT COUNT(*) as `users` FROM `profile`
					WHERE `homepage` LIKE '%s%%'",
					dbesc($site['base_url'])
				);
				if(count($r)){
					$site['users'] = $r[0]['users'];
				}
				
				$result .=
					'<span class="health '.health_score_to_name($site['health_score']).'">&hearts;</span> '.
					'<a href="/health/'.$site['id'].'">' . $site['base_url'] . '</a> '.
					'(' . $site['users'] . ')'.
					"<br />\r\n";
			}
			
			
		}
		
		else {
			$result = 'No results';
		}
		
	}
	
	$tpl .= file_get_contents('view/health_search.tpl');
	return replace_macros($tpl, array(
		'$searched' => $search,
		'$result' => $result
	));
	
}

function health_summary(&$a){
	
	$sites = array();
	
	//Find the user count per site.
	$r = q("SELECT `homepage` FROM `profile` WHERE 1");
	if(count($r)) {
		foreach($r as $rr) {
			$site = parse_site_from_url($rr['homepage']);
			if($site) {
				if(!isset($sites[$site]))
					$sites[$site] = 0;		
				$sites[$site] ++;
			}
		}
	}
	
	//See if we have a health for them.
	$sites_with_health = array();
	$site_healths = array();
	
	$r = q("SELECT * FROM `site-health` WHERE `reg_policy`='REGISTER_OPEN'");
	if(count($r)) {
		foreach($r as $rr) {
			$sites_with_health[$rr['base_url']] = (($sites[$rr['base_url']] / 100) + 10) * intval($rr['health_score']);
			$site_healths[$rr['base_url']] = $rr;
		}
	}
	
	arsort($sites_with_health);
	$total = 0;
	$public_sites = '';
	foreach($sites_with_health as $k => $v)
	{
		
		//Stop at unhealthy sites.
		$site = $site_healths[$k];
		if($site['health_score'] <= 20) break;
		
		//Skip small sites.
		$users = $sites[$k];
		if($users < 10) continue;
		
		$public_sites .=
			'<span class="health '.health_score_to_name($site['health_score']).'">&hearts;</span> '.
			'<a href="/health/'.$site['id'].'">' . $k . '</a> '.
			'(' . $users . ')'.
			"<br />\r\n";
		$total ++;
		
	}
	$public_sites .= "<br>Total: $total<br />\r\n";
	
	$tpl .= file_get_contents('view/health_summary.tpl');
	return replace_macros($tpl, array(
		'$versions' => $versions,
		'$public_sites' => $public_sites
	));
	
}

function health_details($a, $id)
{
	
	//The overall health status.
	$r = q(
		"SELECT * FROM `site-health`
		WHERE `id`=%u",
		intval($id)
	);
	if(!count($r)){
		$a->error = 404;
		return;
	}
	
	$site = $r[0];
	
	//Figure out SSL state.
	$urlMeta = parse_url($site['base_url']);
	if($urlMeta['scheme'] !== 'https'){
		$ssl_state = 'No';
	}else{
		switch ($site['ssl_state']) {
			case null: $ssl_state = 'Yes, but not yet verified.'; break;
			case '0': $ssl_state = 'Certificate error!'; break;
			case '1': $ssl_state = '&radic; Yes, verified.'; break;
		}
		$ssl_state .= ' <a href="https://www.ssllabs.com/ssltest/analyze.html?d='.$urlMeta['host'].'" target="_blank">Detailed test</a>';
	}
	
	//Get user count.
	$site['users'] = 0;
	$r = q(
		"SELECT COUNT(*) as `users` FROM `profile`
		WHERE `homepage` LIKE '%s%%'",
		dbesc($site['base_url'])
	);
	if(count($r)){
		$site['users'] = $r[0]['users'];
	}
	
	//Get avg probe speed.
	$r = q(
		"SELECT AVG(`request_time`) as `avg_probe_time` FROM `site-probe`
		WHERE `site_health_id` = %u",
		intval($site['id'])
	);
	if(count($r)){
		$site['avg_probe_time'] = $r[0]['avg_probe_time'];
	}
	
	//Get scraping / submit speeds.
	$r = q(
		"SELECT
			AVG(`request_time`) as `avg_profile_time`,
			AVG(`scrape_time`) as `avg_scrape_time`,
			AVG(`photo_time`) as `avg_photo_time`,
			AVG(`total_time`) as `avg_submit_time`
		FROM `site-scrape`
		WHERE `site_health_id` = %u",
		intval($site['id'])
	);
	if(count($r)){
		$site['avg_profile_time'] = $r[0]['avg_profile_time'];
		$site['avg_scrape_time'] = $r[0]['avg_scrape_time'];
		$site['avg_photo_time'] = $r[0]['avg_photo_time'];
		$site['avg_submit_time'] = $r[0]['avg_submit_time'];
	}
	
	//Get probe speed data.
	$r = q(
		"SELECT `request_time`, `dt_performed` FROM `site-probe`
		WHERE `site_health_id` = %u",
		intval($site['id'])
	);
	if(count($r)){
		//Include graphael line charts.
		$a->page['htmlhead'] .= '<script type="text/javascript" src="'.$a->get_baseurl().'/js/raphael/raphael.js"></script>'.PHP_EOL;
		$a->page['htmlhead'] .= '<script type="text/javascript" src="'.$a->get_baseurl().'/js/raphael/g_raphael.js"></script>'.PHP_EOL;
		$a->page['htmlhead'] .= '<script type="text/javascript" src="'.$a->get_baseurl().'/js/raphael/g_line.js?v=0.51"></script>';
		$speeds = array();
		$times = array();
		$mintime = time();
		foreach($r as $row){
			$speeds[] = $row['request_time'];
			$time = strtotime($row['dt_performed']);
			$times[] = $time;
			if($mintime > $time) $mintime = $time;
		}
		for($i=0; $i < count($times); $i++){
			$times[$i] -= $mintime;
			$times[$i] = floor($times[$i] / (24*3600));
		}
		$a->page['htmlhead'] .=
			'<script type="text/javascript">
				jQuery(function($){
					
					var r = Raphael("probe-chart")
						, x = ['.implode(',', $times).']
						, y = ['.implode(',', $speeds).']
					;
					
					r.linechart(30, 15, 400, 300, x, [y], {symbol:"circle", axis:"0 0 0 1", shade:true, width:1.5}).hoverColumn(function () {
            this.tags = r.set();
            for (var i = 0, ii = this.y.length; i < ii; i++) {
              this.tags.push(r.popup(this.x, this.y[i], this.values[i]+"ms", "right", 5).insertBefore(this).attr([{ fill: "#eee" }, { fill: this.symbols[i].attr("fill") }]));
            }
	        }, function () {
            this.tags && this.tags.remove();
	        });
					
				});
			</script>';
	}
	
	//Get scrape speed data.
	$r = q(
		"SELECT AVG(`total_time`) as `avg_time`, date(`dt_performed`) as `date` FROM `site-scrape`
		WHERE `site_health_id` = %u GROUP BY `date`",
		intval($site['id'])
		// date('Y-m-d H:i:s', time()-(3*24*3600)) //Max 3 days old.
	);
	if($r && count($r)){
		//Include graphael line charts.
		$a->page['htmlhead'] .= '<script type="text/javascript" src="'.$a->get_baseurl().'/js/raphael/raphael.js"></script>'.PHP_EOL;
		$a->page['htmlhead'] .= '<script type="text/javascript" src="'.$a->get_baseurl().'/js/raphael/g_raphael.js"></script>'.PHP_EOL;
		$a->page['htmlhead'] .= '<script type="text/javascript" src="'.$a->get_baseurl().'/js/raphael/g_line.js?v=0.51"></script>';
		$speeds = array();
		$times = array();
		$mintime = time();
		foreach($r as $row){
			$speeds[] = $row['avg_time'];
			$time = strtotime($row['date']);
			$times[] = $time;
			if($mintime > $time) $mintime = $time;
		}
		for($i=0; $i < count($times); $i++){
			$times[$i] -= $mintime;
			$times[$i] = floor($times[$i] / (24*3600));
		}
		$a->page['htmlhead'] .=
			'<script type="text/javascript">
				jQuery(function($){
					
					var r = Raphael("scrape-chart")
						, x = ['.implode(',', $times).']
						, y = ['.implode(',', $speeds).']
					;
					
					r.linechart(30, 15, 400, 300, x, [y], {shade:true, axis:"0 0 0 1", width:1}).hoverColumn(function () {
            this.tags = r.set();
            for (var i = 0, ii = this.y.length; i < ii; i++) {
              this.tags.push(r.popup(this.x, this.y[i], Math.round(this.values[i])+"ms", "right", 5).insertBefore(this));
            }
	        }, function () {
            this.tags && this.tags.remove();
	        });
					
				});
			</script>';
	}
	
	//Nice name for registration policy.
	switch ($site['reg_policy']) {
		case 'REGISTER_OPEN': $policy = "Open"; break;
		case 'REGISTER_APPROVE': $policy = "Admin approved"; break;
		case 'REGISTER_CLOSED': $policy = "Closed"; break;
		default: $policy = $site['reg_policy']; break;
	}
	
	$tpl .= file_get_contents('view/health_details.tpl');
	return replace_macros($tpl, array(
		'$name' => $site['name'],
		'$policy' => $policy,
		'$site_info' => $site['info'],
		'$base_url' => $site['base_url'],
		'$health_score' => $site['health_score'],
		'$health_name' => health_score_to_name($site['health_score']),
		'$no_scrape_support' => !empty($site['no_scrape_url']) ? '&radic; Supports noscrape' : '',
		'$dt_first_noticed' => $site['dt_first_noticed'],
		'$dt_last_seen' => $site['dt_last_seen'],
		'$version' => $site['version'],
		'$plugins' => $site['plugins'],
		'$reg_policy' => $site['reg_policy'],
		'$info' => $site['info'],
		'$admin_name' => $site['admin_name'],
		'$admin_profile' => $site['admin_profile'],
		'$users' => $site['users'],
		'$ssl_state' => $ssl_state,
		'$avg_probe_time' => round($site['avg_probe_time']),
		'$avg_profile_time' => round($site['avg_profile_time']),
		'$avg_scrape_time' => round($site['avg_scrape_time']),
		'$avg_photo_time' => round($site['avg_photo_time']),
		'$avg_submit_time' => round($site['avg_submit_time'])
	));
