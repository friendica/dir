<?php

use Friendica\Directory\Rendering\View;

require_once('include/site-health.php');

function servers_content(&$a) {
	
	$sites = array();
	
	//Find the user count per site.
	$r = q("SELECT `homepage` FROM `profile`");
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
	
	//See if we have a health for them AND they provide SSL.
	$sites_with_health = array();
	$site_healths = array();
	
	$r = q("SELECT * FROM `site-health` WHERE `reg_policy`='REGISTER_OPEN' AND `ssl_state` = 1");
	if(count($r)) {
		foreach($r as $rr) {
			$sites_with_health[$rr['base_url']] = (($sites[$rr['base_url']] / 100) + 10) * intval($rr['health_score']);
			$site_healths[$rr['base_url']] = $rr;
		}
	}
	
	arsort($sites_with_health);
	$total = 0;
	$public_sites = array();
	foreach($sites_with_health as $k => $v)
	{
		
		//Stop at unhealthy sites.
		$site = $site_healths[$k];
		if($site['health_score'] <= 20) break;
		
		//Skip small sites.
		$users = $sites[$k];
		if($users < 5) continue;
		
		//Add health score name and user count.
		$site['health_score_name'] = health_score_to_name($site['health_score']);
		$site['users'] = $users;
		
		//Figure out what this server supports.
		$plugins = explode("\r\n", $site['plugins']);
		$site['plugins'] = $plugins;
		$hasPlugin = function(array $input)use($plugins){
			return !!count(array_intersect($input, $plugins));
		};
		
		$site['supports'] = array(
			'HTTPS' => $site['ssl_state'] == 1,
			'Twitter' => $hasPlugin(array('buffer', 'twitter')),
			'Google+' => $hasPlugin(array('buffer', 'gpluspost')),
			'RSS/Atom' => true, //Built-in.
			'App.net' => $hasPlugin(array('appnet', 'appnetpost')),
			'Diaspora*' => $hasPlugin(array('diaspora')),
			'pump.io' => $hasPlugin(array('pumpio')),
			'StatusNet' => $hasPlugin(array('statusnet')),
			'Tumblr' => $hasPlugin(array('tumblr')),
			'Blogger' => $hasPlugin(array('blogger')),
			'Dreamwidth' => $hasPlugin(array('dwpost')),
			'Wordpress' => $hasPlugin(array('wppost')),
			'LiveJournal' => $hasPlugin(array('ljpost')),
			'Insanejournal' => $hasPlugin(array('ijpost')),
			'Libertree' => $hasPlugin(array('libertree'))
		);
		
		//Subset of the full support list, to show popular items.
		$site['popular_supports'] = array(
			'HTTPS' => $site['supports']['HTTPS'],
			'Twitter' => $site['supports']['Twitter'],
			'Google+' => $site['supports']['Google+'],
			'Wordpress' => $site['supports']['Wordpress']
		);
		
		//For practical usage.
		$site['less_popular_supports'] = array_diff_assoc($site['supports'], $site['popular_supports']);
		
		//Get the difference.
		$site['supports_more'] = 0;
		foreach ($site['supports'] as $key => $value){
			if($value && !array_key_exists($key, $site['popular_supports'])){
				$site['supports_more']++;
			}
		}
		
		//Push to results.
		$public_sites[] = $site;
		
		//Count the result.
		$total ++;
		
	}
	
	//In case we asked for a surprise, pick a random one from the top 10! :D
	if($a->argv[1] == 'surprise'){
		$max = min(count($public_sites), 10);
		$i = mt_rand(0, $max-1);
		$surpriseSite = $public_sites[$i];
		header('Location:'.$surpriseSite['base_url'].'/register');
		exit;
	}
	
	
	//Show results.
    $view = new View('servers');
    
    $view->output(array(
        'total' => number_format($total),
        'sites' => $public_sites
    ));
	
}
