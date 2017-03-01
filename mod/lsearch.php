<?php

function lsearch_init(&$a) {

    $perpage = (($_REQUEST['n']) ? $_REQUEST['n'] : 80);
    $page = (($_REQUEST['p']) ? intval($_REQUEST['p'] - 1) : 0);
    $startrec = (($page+1) * $perpage) - $perpage;

    $search = trim($_REQUEST['search']);
    if(! strlen($search))
        killme();

	if($search)
		$search = dbesc(escape_tags($search));


	$sql_extra = ((strlen($search)) ? " AND ( `name` REGEXP '$search' OR `homepage` REGEXP '$search' OR `tags` REGEXP '$search' 
		or `region` REGEXP '$search' or `country-name` regexp '$search' ) " : "");

	$r = q("SELECT COUNT(*) AS `total` FROM `profile` WHERE 1 $sql_extra ");
	if(count($r))
        $total = $r[0]['total'];


	$r = q("SELECT * FROM `profile` WHERE 1 $sql_extra ORDER BY `name` ASC LIMIT %d, %d ",
		intval($startrec),
		intval($perpage)
	);

	$results = array();

	if(count($r)) {
		foreach($r as $rr)
	        $results[] = array('name' => $rr['name'], 'url' => $rr['homepage'], 'photo' => $a->get_baseurl() . '/photo/' . $rr['id'], 'tags' => $rr['tags']);
    }

    $output = array('total' => $total, 'items_page' => $perpage, 'page' => $page + 1, 'results' => $results);

    echo json_encode($output);

    killme();
}