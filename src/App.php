<?php

namespace Friendica\Directory;

class App
{
	public $module_loaded = false;
	public $query_string;
	public $config;
	public $page;
	public $profile;
	public $user;
	public $cid;
	public $contact;
	public $content;
	public $data;
	public $error = false;
	public $cmd;
	public $argv;
	public $argc;
	public $module;
	public $pager;
	public $strings;
	public $path;

	private $scheme;
	private $hostname;
	private $baseurl;

	public function __construct()
	{
		$this->config = array();
		$this->page = array();
		$this->pager = array();

		$this->scheme = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'])) ? 'https' : 'http');
		$this->hostname = str_replace('www.', '', $_SERVER['SERVER_NAME']);
		set_include_path(get_include_path()
				. PATH_SEPARATOR . "include/$this->hostname"
				. PATH_SEPARATOR . 'include'
				. PATH_SEPARATOR . '.');

		if (substr($_SERVER['QUERY_STRING'], 0, 2) == "q=") {
			$_SERVER['QUERY_STRING'] = substr($_SERVER['QUERY_STRING'], 2);
		}

		$this->query_string = $_SERVER['QUERY_STRING'];

		$q = isset($_GET['q']) ? $_GET['q'] : '';
		$this->cmd = trim($q, '/');

		$this->argv = explode('/', $this->cmd);
		$this->argc = count($this->argv);
		if ((array_key_exists('0', $this->argv)) && strlen($this->argv[0])) {
			$this->module = $this->argv[0];
		} else {
			$this->module = 'directory';
		}

		$this->pager['page'] = ((x($_GET, 'page')) ? $_GET['page'] : 1);
		$this->pager['itemspage'] = 50;
		$this->pager['start'] = ($this->pager['page'] * $this->pager['itemspage']) - $this->pager['itemspage'];
		$this->pager['total'] = 0;
	}

	public function get_baseurl($ssl = false)
	{
		if (strlen($this->baseurl)) {
			return $this->baseurl;
		}

		$this->baseurl = (($ssl) ? 'https' : $this->scheme) . "://" . $this->hostname
				. ((isset($this->path) && strlen($this->path)) ? '/' . $this->path : '');
		return $this->baseurl;
	}

	public function set_baseurl($url)
	{
		$this->baseurl = $url;
		$this->hostname = basename($url);
	}

	public function get_hostname()
	{
		return $this->hostname;
	}

	public function set_hostname($h)
	{
		$this->hostname = $h;
	}

	public function set_path($p)
	{
		$this->path = ltrim(trim($p), '/');
	}

	public function get_path()
	{
		return $this->path;
	}

	public function set_pager_total($n)
	{
		$this->pager['total'] = intval($n);
	}

	public function set_pager_itemspage($n)
	{
		$this->pager['itemspage'] = intval($n);
		$this->pager['start'] = ($this->pager['page'] * $this->pager['itemspage']) - $this->pager['itemspage'];
	}

	public function init_pagehead()
	{
		if (file_exists("view/head.tpl")) {
			$s = file_get_contents("view/head.tpl");
		}
		$this->page['htmlhead'] = replace_macros($s, array(
			'$baseurl' => $this->get_baseurl()
		));
	}
}