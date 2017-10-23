<?php

use Friendica\Directory\App;
use Friendica\Directory\Rendering\View;
use Friendica\Directory\Helper\Search as SearchHelper;
use Friendica\Directory\Helper\Profile as ProfileHelper;

require_once 'include/widget.php';

function directory_init(App $a)
{
	$a->set_pager_itemspage(30);

	$a->page['aside'] .= tags_widget();
	$a->page['aside'] .= country_widget();
}

function directory_content(App $a)
{
	$forums = false;
	if ($a->argc == 2 && $a->argv[1] === 'forum') {
		$forums = true;
	}

	$alpha = false;
	if (isset($_GET['alpha']) && $_GET['alpha'] == 1) {
		$alpha = true;
	}

	$tpl = file_get_contents('view/directory_header.tpl');

	$o = replace_macros($tpl, array(
		'$header'  => t('Global Directory'),
		'$submit'  => t('Find'),
		'$forum'   => $a->get_baseurl() . (($forums) ? '' : '/directory/forum'),
		'$toggle'  => (($forums) ? t('Show People') : t('Show Community Forums')),
		'$alpha'   => (($alpha) ? t('Updated order') : t('Alphabetic order')),
		'$alink'   => (($alpha) ? str_replace('&alpha=1', '', $a->query_string) : $a->query_string . "&alpha=1"),
		'$args'    => (($forums) ? '/forum' : ''),
	));

	$sql_extra = '';
	if ($forums) {
		$sql_extra .= ' AND `comm` = 1 ';
	}

	$sql_extra = str_replace('%', '%%', $sql_extra);

	$r = q("SELECT COUNT(*) AS `total` FROM `profile` WHERE `censored` = 0 AND `available` = 1 $sql_extra ");
	if (count($r)) {
		$total = $r[0]['total'];
		$a->set_pager_total($total);
	}

	if ($alpha) {
		$order = ' ORDER BY `name` ASC ';
	} else {
		$order = ' ORDER BY `updated` DESC, `id` DESC ';
	}

	$r = q("SELECT * FROM `profile` WHERE `censored` = 0 AND `available` = 1 $sql_extra $order LIMIT %d , %d ",
		intval($a->pager['start']),
		intval($a->pager['itemspage'])
	);

	//Show results.
	$view = new View('directory');

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
		'filter'  => $forums ? 'forums' : '',
	));

	killme();
}
