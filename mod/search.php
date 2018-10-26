<?php

use Friendica\Directory\App;
use Friendica\Directory\Rendering\View;
use Friendica\Directory\Helper\Search as SearchHelper;
use Friendica\Directory\Helper\Profile as ProfileHelper;

require_once 'include/widget.php';

function search_init(App $a)
{
	$a->set_pager_itemspage(30);

	$a->page['aside'] .= tags_widget($a);
	$a->page['aside'] .= country_widget($a);
}

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

	if (strpos($search, ' ') === false) {
		$search .= '*';
	}

	//Run our query.
	$search = dbesc($search);
	$search = str_replace('%', '%%', $search);

	$sql_where = "WHERE MATCH (`name`, `pdesc`, `homepage`, `locality`, `region`, `country-name`, `tags` )
AGAINST ('$search' IN BOOLEAN MODE)
AND NOT `censored`
AND `available`";

	if (!is_null($community)) {
		$sql_where .= '
AND `comm` = ' . intval($community);
	}

	$total = 0;
	$r = q("SELECT COUNT(*) AS `total`
FROM `profile`
$sql_where");
	if (count($r)) {
		$total = $r[0]['total'];
		$a->set_pager_total($total);
	}

	$query = "SELECT *
FROM `profile`
$sql_where
ORDER BY `filled_fields` DESC, `last_activity` DESC, `updated` DESC
LIMIT %d, %d";

	$r = q($query,
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
		'aside'   => $a->page['aside'],
		'total'   => number_format($total),
		'results' => $r,
		'filter'  => $filter,
		'query'   => x($_GET, 'query') ? $_GET['query'] : ''
	));

	killme();
}
