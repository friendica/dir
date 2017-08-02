<?php

use Friendica\Directory\Rendering\View;

if (!function_exists('stats_content')) {
	function stats_content(&$a)
	{
		$view = new View('stats');
		$view->output();

		killme();
	}
}

