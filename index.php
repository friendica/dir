<?php

use Friendica\Directory\App;

require_once 'boot.php';

$a = new App();

error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);
ini_set('error_log', 'php.out');
ini_set('log_errors', '1');
ini_set('display_errors', '0');

$debug_text = '';

require_once '.htconfig.php';

require_once 'dba.php';

$db = new dba($db_host, $db_user, $db_pass, $db_data);
unset($db_host, $db_user, $db_pass, $db_data);

$a->init_pagehead();
$a->page['aside'] = '';

session_start();

if ((x($_SESSION, 'authenticated')) || (x($_POST, 'auth-params')) || ($a->module === 'login')) {
	require 'auth.php';
}

$dreamhost_error_hack = 1;

if (x($_GET, 'zrl')) {
	$_SESSION['my_url'] = $_GET['zrl'];
	$a->query_string = preg_replace('/[\?&]*zrl=(.*?)([\?&]|$)/is', '', $a->query_string);
}

if (strlen($a->module)) {
	if (file_exists("mod/{$a->module}.php")) {
		include("mod/{$a->module}.php");
		$a->module_loaded = true;
	}

	if (!$a->module_loaded) {
		if ((x($_SERVER, 'QUERY_STRING')) && ($_SERVER['QUERY_STRING'] === 'q=internal_error.html') && isset($dreamhost_error_hack)) {
			goaway($a->get_baseurl() . $_SERVER['REQUEST_URI']);
		}
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 ' . t('Not Found'));
		notice(t('Page not found') . EOL);
	}
}

if ($a->module_loaded) {
	$a->page['page_title'] = $a->module;

	if (function_exists($a->module . '_init')) {
		$func = $a->module . '_init';
		$func($a);
	}

	if (($_SERVER['REQUEST_METHOD'] == 'POST') && (!$a->error) && (function_exists($a->module . '_post')) && (!x($_POST, 'auth-params'))) {
		$func = $a->module . '_post';
		$func($a);
	}

	if ((!$a->error) && (function_exists($a->module . '_afterpost'))) {
		$func = $a->module . '_afterpost';
		$func($a);
	}

	if ((!$a->error) && (function_exists($a->module . '_content'))) {
		$func = $a->module . '_content';
		$a->page['content'] = $func($a);
	}
}

// report anything important happening

if (x($_SESSION, 'sysmsg')) {
	if (stristr($_SESSION['sysmsg'], t('Permission denied'))) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 403 ' . t('Permission denied.'));
	}

	if (!isset($a->page['content'])) {
		$a->page['content'] = '';
	}

	$a->page['content'] = '<div id="sysmsg" class="error-message">' . $_SESSION['sysmsg'] . '</div>' . PHP_EOL
		. $a->page['content'];
	unset($_SESSION['sysmsg']);
}

// build page

$a->page['htmlhead'] = replace_macros($a->page['htmlhead'], array(
	'$stylesheet' => $a->get_baseurl() . '/view/theme/'
	. ((x($_SESSION, 'theme')) ? $_SESSION['theme'] : 'default')
	. '/style.css'
	));

$page = $a->page;
$profile = $a->profile;

header('Content-type: text/html; charset=utf-8');

$template = 'view/'
	. ((x($a->page, 'template')) ? $a->page['template'] : 'default' )
	. '.php';

require_once $template;

killme();
