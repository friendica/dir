<?php

require_once 'include/session.php';

require_once 'vendor/autoload.php';

set_time_limit(0);

define('BUILD_ID', 1000);

define('EOL', "<br />\r\n");

define('REGISTER_CLOSED',  0);
define('REGISTER_APPROVE', 1);
define('REGISTER_OPEN',    2);

define('DIRECTION_NONE', 0);
define('DIRECTION_IN',   1);
define('DIRECTION_OUT',  2);
define('DIRECTION_BOTH', 3);

define('NOTIFY_INTRO',   0x0001);
define('NOTIFY_CONFIRM', 0x0002);
define('NOTIFY_WALL',    0x0004);
define('NOTIFY_COMMENT', 0x0008);
define('NOTIFY_MAIL',    0x0010);

define('NAMESPACE_DFRN', 'http://purl.org/macgirvin/dfrn/1.0');

/**
 * log levels
 */
define('LOGGER_NORMAL', 0);
define('LOGGER_TRACE',  1);
define('LOGGER_DEBUG',  2);
define('LOGGER_DATA',   3);
define('LOGGER_ALL',    4);

if (!function_exists('x')) {
	function x($s, $k = null)
	{
		if ($k != null) {
			if ((is_array($s)) && (array_key_exists($k, $s))) {
				if ($s[$k]) {
					return (int) 1;
				}
				return (int) 0;
			}
			return false;
		} else {
			if (isset($s)) {
				if ($s) {
					return (int) 1;
				}
				return (int) 0;
			}
			return false;
		}
	}
}

if (!function_exists('system_unavailable')) {
	function system_unavailable()
	{
		include('system_unavailable.php');
		killme();
	}
}

if (!function_exists('logger')) {
	function logger($msg, $level = 0)
	{
		$debugging = 1;
		$loglevel = LOGGER_ALL;
		$logfile = 'logfile.out';

		if ((!$debugging) || (!$logfile) || ($level > $loglevel)) {
			return;
		}
		require_once('include/datetime.php');

		@file_put_contents($logfile, datetime_convert() . ':' . ' ' . $msg . "\n", FILE_APPEND);
		return;
	}
}


if (!function_exists('replace_macros')) {
	function replace_macros($s, $r)
	{
		$search = array();
		$replace = array();

		if (is_array($r) && count($r)) {
			foreach ($r as $k => $v) {
				$search[] = $k;
				$replace[] = $v;
			}
		}
		return str_replace($search, $replace, $s);
	}
}


if (!function_exists('load_translation_table')) {
	function load_translation_table($lang)
	{
		global $a;
	}
}

if (!function_exists('t')) {
	function t($s)
	{
		global $a;

		if ($a->strings[$s]) {
			return $a->strings[$s];
		}
		return $s;
	}
}

if (!function_exists('fetch_url')) {
	function fetch_url($url, $binary = false, $timeout = 20)
	{
		$ch = curl_init($url);
		if (!$ch) {
			return false;
		}

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, max(intval($timeout), 1)); //Minimum of 1 second timeout.
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 8);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($binary) {
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$s = curl_exec($ch);
		curl_close($ch);
		return($s);
	}
}

if (!function_exists('post_url')) {
	function post_url($url, $params)
	{
		$ch = curl_init($url);
		if (!$ch) {
			return false;
		}

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 8);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$s = curl_exec($ch);
		curl_close($ch);
		return($s);
	}
}

if (!function_exists('random_string')) {
	function random_string()
	{
		return(hash('sha256', uniqid(rand(), true)));
	}
}

if (!function_exists('notags')) {
	function notags($string)
	{
		// protect against :<> with high-bit set
		return(str_replace(array("<", ">", "\xBA", "\xBC", "\xBE"), array('[', ']', '', '', ''), $string));
	}
}

if (!function_exists('escape_tags')) {
	function escape_tags($string)
	{
		return(htmlspecialchars($string));
	}
}

if (!function_exists('login')) {
	function login($register = false)
	{
		$o = "";
		$register_html = (($register) ? file_get_contents("view/register-link.tpl") : "");


		if (x($_SESSION, 'authenticated')) {
			$o = file_get_contents("view/logout.tpl");
		} else {
			$o = file_get_contents("view/login.tpl");

			$o = replace_macros($o, array('$register_html' => $register_html));
		}
		return $o;
	}
}

if (!function_exists('killme')) {
	function killme()
	{
		session_write_close();
		exit;
	}
}

if (!function_exists('goaway')) {
	function goaway($s)
	{
		header("Location: $s");
		killme();
	}
}

if (!function_exists('local_user')) {
	function local_user()
	{
		if ((x($_SESSION, 'authenticated')) && (x($_SESSION, 'uid'))) {
			return $_SESSION['uid'];
		}
		return false;
	}
}

if (!function_exists('notice')) {
	function notice($s)
	{
		if (!isset($_SESSION['sysmsg'])) {
			$_SESSION['sysmsg'] = '';
		}
		$_SESSION['sysmsg'] .= $s;
	}
}

if (!function_exists('hex2bin')) {
	function hex2bin($s)
	{
		return(pack("H*", $s));
	}
}

if (!function_exists('paginate')) {
	function paginate(&$a)
	{
		$o = '';
		$stripped = preg_replace("/&page=[0-9]*/", "", $a->query_string);
		$stripped = str_replace('q=', '', $stripped);
		$stripped = trim($stripped, '/');
		$pagenum = $a->pager['page'];
		$url = $a->get_baseurl() . '/' . $stripped;

		if ($a->pager['total'] > $a->pager['itemspage']) {
			$o .= '<div class="pager">';
			if ($a->pager['page'] != 1) {
				$o .= '<span class="pager_prev">' . "<a href=\"$url" . '&page=' . ($a->pager['page'] - 1) . '">' . t('prev') . '</a></span> ';
			}

			$o .= "<span class=\"pager_first\"><a href=\"$url" . "&page=1\">" . t('first') . "</a></span> ";

			$numpages = $a->pager['total'] / $a->pager['itemspage'];

			$numstart = 1;
			$numstop = $numpages;

			if ($numpages > 14) {
				$numstart = (($pagenum > 7) ? ($pagenum - 7) : 1);
				$numstop = (($pagenum > ($numpages - 7)) ? $numpages : ($numstart + 14));
			}

			for ($i = $numstart; $i <= $numstop; $i++) {
				if ($i == $a->pager['page']) {
					$o .= '<span class="pager_current">' . (($i < 10) ? '&nbsp;' . $i : $i);
				} else {
					$o .= "<span class=\"pager_n\"><a href=\"$url" . "&page=$i\">" . (($i < 10) ? '&nbsp;' . $i : $i) . "</a>";
				}
				$o .= '</span> ';
			}

			if (($a->pager['total'] % $a->pager['itemspage']) != 0) {
				if ($i == $a->pager['page']) {
					$o .= '<span class="pager_current">' . (($i < 10) ? '&nbsp;' . $i : $i);
				} else {
					$o .= "<span class=\"pager_n\"><a href=\"$url" . "&page=$i\">" . (($i < 10) ? '&nbsp;' . $i : $i) . "</a>";
				}
				$o .= '</span> ';
			}

			$lastpage = (($numpages > intval($numpages)) ? intval($numpages) + 1 : $numpages);
			$o .= "<span class=\"pager_last\"><a href=\"$url" . "&page=$lastpage\">" . t('last') . "</a></span> ";

			if (($a->pager['total'] - ($a->pager['itemspage'] * $a->pager['page'])) > 0) {
				$o .= '<span class="pager_next">' . "<a href=\"$url" . "&page=" . ($a->pager['page'] + 1) . '">' . t('next') . '</a></span>';
			}
			$o .= '</div>' . "\r\n";
		}
		return $o;
	}
}

function get_my_url()
{
	if (x($_SESSION, 'my_url')) {
		return $_SESSION['my_url'];
	}
	return false;
}

function zrl($s, $force = false)
{
	if (!strlen($s)) {
		return $s;
	}
	if ((!strpos($s, '/profile/')) && (!$force)) {
		return $s;
	}
	$achar = strpos($s, '?') ? '&' : '?';
	$mine = get_my_url();
	if ($mine and ! link_compare($mine, $s)) {
		return $s . $achar . 'zrl=' . urlencode($mine);
	}
	return $s;
}

if (!function_exists('link_compare')) {
	function link_compare($a, $b)
	{
		if (strcasecmp(normalise_link($a), normalise_link($b)) === 0) {
			return true;
		}
		return false;
	}
}

if (!function_exists('normalise_link')) {
	function normalise_link($url)
	{
		$ret = str_replace(array('https:', '//www.'), array('http:', '//'), $url);
		return(rtrim($ret, '/'));
	}
}
