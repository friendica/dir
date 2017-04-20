<?php

require_once 'include/widget.php';

function directory_init(App $a)
{
    $a->set_pager_itemspage(80);

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
    if ($_GET['alpha'] == 1) {
        $alpha = true;
    }

    $search = ((x($_GET, 'search')) ? notags(trim($_GET['search'])) : '');

    if ($_GET['submit'] === t('Clear')) {
        goaway($a->get_baseurl());
    }

    if ($search) {
        $alpha = true;
    }

    $tpl .= file_get_contents('view/directory_header.tpl');

    $o .= replace_macros($tpl, array(
        '$search'  => $search,
        '$header'  => t('Global Directory'),
        '$submit'  => t('Find'),
        '$clear'   => t('Clear'),
        '$forum'   => $a->get_baseurl() . (($forums) ? '' : '/directory/forum'),
        '$toggle'  => (($forums) ? t('Show People') : t('Show Community Forums')),
        '$alpha'   => (($alpha) ? t('Updated order') : t('Alphabetic order')),
        '$alink'   => (($alpha) ? str_replace('&alpha=1', '', $a->query_string) : $a->query_string . "&alpha=1"),
        '$args'    => (($forums) ? '/forum' : ''),
        '$finding' => (strlen($search) ? '<h4>' . t('Search for: ') . "'" . $search . "'" . '</h4>' : "")
    ));

    if ($search) {
        $search = dbesc($search . '*');
    }
    $sql_extra = ((strlen($search)) ? " AND MATCH (`name`, `pdesc`, `homepage`, `locality`, `region`, `country-name`, `tags` )
		AGAINST ('$search' IN BOOLEAN MODE) " : "");

    if ($forums) {
        $sql_extra .= " and comm = 1 ";
    }

    $sql_extra = str_replace('%', '%%', $sql_extra);

    $r = q("SELECT COUNT(*) AS `total` FROM `profile` WHERE `censored` = 0 $sql_extra ");
    if (count($r)) {
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

    if (count($r)) {
        $tpl = file_get_contents('view/directory_item.tpl');

        foreach ($r as $rr) {
            $pdesc = (($rr['pdesc']) ? $rr['pdesc'] . '<br />' : '');

            $details = '';
            if (strlen($rr['locality'])) {
                $details .= $rr['locality'];
            }
            if (strlen($rr['region'])) {
                if (strlen($rr['locality'])) {
                    $details .= ', ';
                }
                $details .= $rr['region'];
            }
            if (strlen($rr['country-name'])) {
                if (strlen($details)) {
                    $details .= ', ';
                }
                $details .= $rr['country-name'];
            }

            $o .= replace_macros($tpl, array(
                '$id'           => $rr['id'],
                '$mod'          => '<div class="moderate"><a href="flag/' . $rr['id'] . '" title="' . t('Flag this entry') . '" ><img src="images/shield_2_16.png" alt="' . t('Flag this entry') . '" title="' . t('Flag this entry') . '"></a></div>',
                '$star'         => (($rr['tags']) ? '<div class="star" title="' . strip_tags($rr['tags']) . '"></div>' : ''),
                '$profile-link' => zrl($rr['homepage']),
                '$photo'        => $a->get_baseurl() . '/photo/' . $rr['id'],
                '$alt-text'     => $rr['name'] . ' ' . '(' . $rr['homepage'] . ')',
                '$name'         => $rr['name'],
                '$pclass'       => (($rr['comm']) ? ' group' : ''),
                '$pgroup'       => (($rr['comm']) ? '<div class="directory-group">' . t('[Public Group]') . '</div>' : ''),
                '$details'      => $pdesc . $details
            ));
        }

		$o .= '<div class="directory-end" ></div>' . PHP_EOL;
        $o .= paginate($a);
    } else {
        notice(t('No matching entries.') . EOL);
    }

    return $o;
}
