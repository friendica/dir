<?php
/*
  Based on a submitted URL, take note of the site it mentions.
  Ensures that the site health will be tracked if it wasn't already.
  If $check_health is set to true, this function may trigger some health checks (CURL requests) when needed.
  Do not enable it unless you have enough execution time to do so.
  But when you do, it's better to check for health whenever a site submits something.
  After all, the highest chance for the server to be online is when it submits activity.
 */
if (!function_exists('notice_site')) {
	function notice_site($url, $check_health = false)
	{
		global $a;

		//Parse the domain from the URL.
		$site = parse_site_from_url($url);

		//Search for it in the site-health table.
		$result = q(
			"SELECT * FROM `site-health` WHERE `base_url`= '%s' ORDER BY `id` ASC LIMIT 1",
			dbesc($site)
		);

		//If it exists, see if we need to update any flags / statuses.
		if (!empty($result) && isset($result[0])) {
			$entry = $result[0];

			//If we are allowed to do health checks...
			if ($check_health) {
				//And the site is in bad health currently, do a check now.
				//This is because you have a high certainty the site may perform better now.
				if ($entry['health_score'] < -40) {
					run_site_probe($entry['id'], $entry);
				}

				//Or if the site has not been probed for longer than the minimum delay.
				//This is to make sure not everything is postponed to the batches.
				elseif (strtotime($entry['dt_last_probed']) < time() - $a->config['site-health']['min_probe_delay']) {
					run_site_probe($entry['id'], $entry);
				}
			}
		}

		//If it does not exist.
		else {

			//Add it and make sure it is ready for probing.
			q(
				"INSERT INTO `site-health` (`base_url`, `dt_first_noticed`) VALUES ('%s', NOW())",
				dbesc($site)
			);

			//And in case we should probe now, do so.
			if ($check_health) {

				$result = q(
					"SELECT * FROM `site-health` WHERE `base_url`= '%s' ORDER BY `id` ASC LIMIT 1",
					dbesc($site)
				);
				if (!empty($result) && isset($result[0])) {
					$entry = $result[0];
					run_site_probe($result[0]['id'], $entry);
				}
			}
		}

		//Give other scripts the site health.
		return isset($entry) ? $entry : false;
	}
}

//Extracts the site from a given URL.
if (!function_exists('parse_site_from_url')) {
	function parse_site_from_url($url)
	{
		//Currently a simple implementation, but may improve over time.
		#TODO: support subdirectories?
		$urlMeta = parse_url($url);
		return $urlMeta['scheme'] . '://' . $urlMeta['host'];
	}
}

//Performs a ping to the given site ID
//You may need to notice the site first before you know it's ID.
//TODO: Probe server location using IP address or using the info the friendica server provides (preferred).
//      If IP needs to be used only provide country information.
//TODO: Check SSLLabs Grade
//      Check needs to be asynchronous, meaning that the check at SSLLabs will be initiated in one run while
//      the results must be fetched later. It might be good to mark sites, where a check has been inititated
//      f.e. using the ssl_grade field. In the next run, results of these sites could be fetched.
if (!function_exists('run_site_probe')) {
	function run_site_probe($id, &$entry_out)
	{
		global $a;

		//Get the site information from the DB, based on the ID.
		$result = q(
			"SELECT * FROM `site-health` WHERE `id`= %u ORDER BY `id` ASC LIMIT 1",
			intval($id)
		);

		//Abort the probe if site is not known.
		if (!$result || !isset($result[0])) {
			logger('Unknown site-health ID being probed: ' . $id);
			throw new \Exception('Unknown site-health ID being probed: ' . $id);
		}

		//Shortcut.
		$entry = $result[0];
		$base_url = $entry['base_url'];
		$probe_location = $base_url . '/friendica/json';

		$net_ping = Net_Ping::factory();
		$net_ping->setArgs(['count' => 5]);
		$result = $net_ping->ping(parse_url($base_url, PHP_URL_HOST));

		if (is_a($result, 'Net_Ping_Result')) {
			$avg_ping = $result->getAvg();
		} else {
			$avg_ping = null;
		}

		//Prepare the CURL call.
		$handle = curl_init();
		$options = array(
			//Timeouts
			CURLOPT_TIMEOUT => max($a->config['site-health']['probe_timeout'], 1), //Minimum of 1 second timeout.
			CURLOPT_CONNECTTIMEOUT => 1,
			//Redirecting
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 8,
			//SSL
			CURLOPT_SSL_VERIFYPEER => true,
			// CURLOPT_VERBOSE => true,
			// CURLOPT_CERTINFO => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
			//Basic request
			CURLOPT_USERAGENT => 'friendica-directory-probe-1.0',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $probe_location
		);
		curl_setopt_array($handle, $options);

		//Probe the site.
		$probe_start = microtime(true);
		$probe_data = curl_exec($handle);
		$probe_end = microtime(true);

		//Check for SSL problems.
		$curl_statuscode = curl_errno($handle);
		$sslcert_issues = in_array($curl_statuscode, array(
			60, //Could not authenticate certificate with known CA's
			83  //Issuer check failed
		));

		//When it's the certificate that doesn't work.
		if ($sslcert_issues) {
			//Probe again, without strict SSL.
			$options[CURLOPT_SSL_VERIFYPEER] = false;

			//Replace the handle.
			curl_close($handle);
			$handle = curl_init();
			curl_setopt_array($handle, $options);

			//Probe.
			$probe_start = microtime(true);
			$probe_data = curl_exec($handle);
			$probe_end = microtime(true);

			//Store new status.
			$curl_statuscode = curl_errno($handle);
		}

		//Gather more meta.
		$time = round(($probe_end - $probe_start) * 1000);
		$status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$type = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
		$info = curl_getinfo($handle);

		//Done with CURL now.
		curl_close($handle);

		if ($time && $avg_ping) {
			$speed_score = max(1, $avg_ping > 10 ? $time / $avg_ping : $time / 50);
		} else {
			$speed_score = null;
		}

		#TODO: if the site redirects elsewhere, notice this site and record an issue.
		$effective_base_url = parse_site_from_url($info['url']);
		$wrong_base_url = $effective_base_url !== $entry['base_url'];

		try {
			$data = json_decode($probe_data);
		} catch (\Exception $ex) {
			$data = false;
		}

		$parse_failed = !$data;

		$parsedDataQuery = '';

		logger('Effective Base URL: ' . $effective_base_url);

		if ($wrong_base_url) {
			$parsedDataQuery .= sprintf(
				"`effective_base_url` = '%s',",
				dbesc($effective_base_url)
			);
		} else {
			$parsedDataQuery .= "`effective_base_url` = NULL,";
		}

		if (!$parse_failed) {
			$given_base_url_match = $data->url == $base_url;

			//Record the probe speed in a probes table.
			q(
				"INSERT INTO `site-probe` (`site_health_id`, `dt_performed`, `request_time`, `avg_ping`, `speed_score`)" .
				"VALUES (%u, NOW(), %u, %u, %u)",
				$entry['id'],
				$time,
				$avg_ping,
				$speed_score
			);

			if (isset($data->addons)) {
				$addons = $data->addons;
			} else {
				// Backward compatibility
				$addons = $data->plugins;
			}

			//Update any health calculations or otherwise processed data.
			$parsedDataQuery .= sprintf(
				"`dt_last_seen` = NOW(),
       `name` = '%s',
       `version` = '%s',
       `addons` = '%s',
       `reg_policy` = '%s',
       `info` = '%s',
       `admin_name` = '%s',
       `admin_profile` = '%s',
      ",
				dbesc($data->site_name),
				dbesc($data->version),
				dbesc(implode("\r\n", $addons)),
				dbesc($data->register_policy),
				dbesc($data->info),
				dbesc($data->admin->name),
				dbesc($data->admin->profile)
			);

			//Did we use HTTPS?
			$urlMeta = parse_url($probe_location);
			if ($urlMeta['scheme'] == 'https') {
				$parsedDataQuery .= sprintf("`ssl_state` = b'%u',", $sslcert_issues ? '0' : '1');
			} else {
				$parsedDataQuery .= "`ssl_state` = NULL,";
			}

			//Do we have a no scrape supporting node? :D
			if (isset($data->no_scrape_url)) {
				$parsedDataQuery .= sprintf("`no_scrape_url` = '%s',", dbesc($data->no_scrape_url));
			}
		}

		//Get the new health.
		$version = $parse_failed ? '' : $data->version;
		$health = health_score_after_probe($entry['health_score'], !$parse_failed, $time, $version, $sslcert_issues);

		//Update the health.
		q("UPDATE `site-health` SET
    `health_score` = '%d',
    $parsedDataQuery
    `dt_last_probed` = NOW()
    WHERE `id` = %d LIMIT 1",
			$health,
			$entry['id']
		);

		//Get the site information from the DB, based on the ID.
		$result = q(
			"SELECT * FROM `site-health` WHERE `id`= %u ORDER BY `id` ASC LIMIT 1",
			$entry['id']
		);

		//Return updated entry data.
		if ($result && isset($result[0])) {
			$entry_out = $result[0];
		}
	}
}

//Determines the new health score after a probe has been executed.
if (!function_exists('health_score_after_probe')) {
	function health_score_after_probe($current, $probe_success, $time = null, $version = null, $ssl_issues = null)
	{
		//Probe failed, costs you 30 points.
		if (!$probe_success) {
			return max($current - 30, -100);
		}

		//A good probe gives you 20 points.
		$current += 20;

		//Speed scoring.
		if (intval($time) > 0) {
			//Pentaly / bonus points.
			if ($time > 800) {
				$current -= 10; //Bad speed.
			} elseif ($time > 400) {
				$current -= 5; //Still not good.
			} elseif ($time > 250) {
				$current += 0; //This is normal.
			} elseif ($time > 120) {
				$current += 5; //Good speed.
			} else {
				$current += 10; //Excellent speed.
			}

			//Cap for bad speeds.
			if ($time > 800) {
				$current = min(40, $current);
			} elseif ($time > 400) {
				$current = min(60, $current);
			}
		}

		//Version check.
		if (!empty($version)) {
			$versionParts = explode('.', $version);

			//Older than 3.x.x?
			//Your score can not go above 30 health.
			if (intval($versionParts[0]) < 3) {
				$current = min($current, 30);
			}

			//Older than 3.3.x?
			elseif (!empty($versionParts[1]) && intval($versionParts[1] < 3)) {
				$current -= 5; //Somewhat outdated.
			}

			#TODO: See if this needs to be more dynamic.
			#TODO: See if this is a proper indicator of health.
		}

		//SSL problems? That's a big deal.
		if ($ssl_issues === true) {
			$current -= 10;
		}

		//Don't go beyond +100 or -100.
		return max(min(100, $current), -100);
	}
}

//Changes a score into a name. Used for classes and such.
if (!function_exists('health_score_to_name')) {
	function health_score_to_name($score)
	{
		if ($score < -50) {
			return 'very-bad';
		} elseif ($score < 0) {
			return 'bad';
		} elseif ($score < 30) {
			return 'neutral';
		} elseif ($score < 50) {
			return 'ok';
		} elseif ($score < 80) {
			return 'good';
		} else {
			return 'perfect';
		}
	}
}
