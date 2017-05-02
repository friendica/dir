<?php

use Friendica\Directory\Rendering\View;
use Friendica\Directory\Helper\Search as SearchHelper;
use Friendica\Directory\Helper\Profile as ProfileHelper;

function search_content(App $a)
{
	//Filters
	$community = null;
	$filter = null;

	if ($a->argc >= 2) {
		$filter = $a->argv[1];
		switch ($filter) {

			case 'forums':
				$community = 1;
				break;

			case 'people':
				$community = 0;
				break;

			default:
				$community = null;
				$filter = null;
				break;
		}
	}

	$alpha = false;
	if (x($_GET, 'alpha') == 1)
		$alpha = true;

	//Query
	$search = ((x($_GET, 'query')) ? notags(trim($_GET['query'])) : '');

	if (empty($search)) {
		goaway('/home');
	}

	if ($search) {
		$alpha = true;
	}

	//Run our query.
	if ($search) {
		$search = dbesc($search . '*');
	}

	$sql_extra = ((strlen($search)) ? " AND MATCH (`name`, `pdesc`, `homepage`, `locality`, `region`, `country-name`, `tags` )
		AGAINST ('$search' IN BOOLEAN MODE) " : "");

	if (!is_null($community)) {
		$sql_extra .= " and comm=" . intval($community) . " ";
	}

	$sql_extra = str_replace('%', '%%', $sql_extra);

	$total = 0;
	$r = q("SELECT COUNT(*) AS `total` FROM `profile` WHERE `censored` = 0 $sql_extra ");
	if (count($r)) {
		$total = $r[0]['total'];
		$a->set_pager_total($r[0]['total']);
	}

	if ($alpha) {
		$order = " order by name asc ";
	} else {
		$order = " order by updated desc, id desc ";
	}

	$r = q("SELECT * FROM `profile` WHERE `censored` = 0 $sql_extra $order LIMIT %d , %d ",
		intval($a->pager['start']),
		intval($a->pager['itemspage'])
	);

	//Show results.
	$view = new View('search');

	$view->addHelper('paginate', function() use ($a) {
		return paginate($a);
	});
	$view->addHelper('photoUrl', ProfileHelper::get('photoUrl'));
	$view->addHelper('filterAllUrl', SearchHelper::get('filterAllUrl'));
	$view->addHelper('filterPeopleUrl', SearchHelper::get('filterPeopleUrl'));
	$view->addHelper('filterForumsUrl', SearchHelper::get('filterForumsUrl'));

	$view->output(array(
		'total'   => number_format($total),
		'results' => $r,
		'filter'  => $filter,
		'query'   => x($_GET, 'query') ? $_GET['query'] : ''
	));
}
