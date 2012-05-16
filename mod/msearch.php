<?php

function msearch_post(&$a) {

	$perpage = (($_POST['n']) ? $_POST['n'] : 80);
	$page = (($_POST['p']) ? intval($_POST['p'] - 1) : 0);
	$startrec = (($page+1) * $perpage) - $perpage;

	$search = $_POST['s'];
	if(! strlen($search))
		killme();

	$r = q("SELECT COUNT(*) AS `total` FROM `profile` WHERE MATCH `tags` AGAINST ('%s') ",
		dbesc($search)
	);
	if(count($r))
		$total = $r[0]['total'];

	$r = q("SELECT MATCH `tags` AGAINST ('%s') AS `score`, `name`, `homepage`,`photo`,`tags` FROM `profile` WHERE MATCH `tags` AGAINST ('%s') ORDER BY `score` DESC LIMIT %d , %d ",
		dbesc($search),
		dbesc($search),
		intval($startrec),
		intval($perpage)
	);

	$results = array();
	if(count($r)) {
		foreach($r as $rr)
			$results[] = array('name' => $rr['name'], 'url' => $rr['homepage'], 'photo' => $rr['photo'], 'tags' => $rr['tags']);
	}

	$output = array('total' => $total, 'items_page' => $perpage, 'page' => $page + 1, 'results' => $results);

	echo json_encode($output);

	killme();

}