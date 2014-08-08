<?php

function sync_content(&$a)
{
  
  header('Content-type: application/json; charset=utf-8');
  
  //When no arguments were given, return a json token to show we support this method.
  if($a->argc < 2){
    echo json_encode(array(
      'pulling_enabled'=>!!$a->config['syncing']['enable_pulling'],
      'pushing_enabled'=>!!$a->config['syncing']['enable_pushing']
    ));
    exit;
  }
  
  //Method switcher here.
  else{
    switch($a->argv[1]){
      case 'pull':
        if(!$a->config['syncing']['enable_pulling']){
          echo json_encode(array('error'=>'Pulling disabled.')); exit;
        }
        switch ($a->argv[2]) {
          case 'all': echo json_encode(do_pull_all()); exit;
          case 'since': echo json_encode(do_pull($a->argv[3])); exit;
        }
      default: echo json_encode(array('error'=>'Unknown method.')); exit;
    }
  }
  
}

function do_pull($since)
{
  
  if(!intval($since)){
    return array('error' => 'Must set a since timestamp.');
  }
  
  //Recently modified items.
  $r = q("SELECT * FROM `sync-timestamps` WHERE `modified` > '%s'", date('Y-m-d H:i:s', intval($since)));
  
  //This removes all duplicates.
  $profiles = array();
  foreach($r as $row) $profiles[$row['url']] = $row['url'];
  
  //This removes the keys, so it's a flat array.
  $results = array_values($profiles);
  
  //Format it nicely.
  return array(
    'now' => time(),
    'count' => count($results),
    'results' => $results
  );
  
}

function do_pull_all()
{
  
  //Find all the profiles.
  $r = q("SELECT `homepage` FROM `profile`");
  
  //This removes all duplicates.
  $profiles = array();
  foreach($r as $row) $profiles[$row['homepage']] = $row['homepage'];
  
  //This removes the keys, so it's a flat array.
  $results = array_values($profiles);
  
  //Format it nicely.
  return array(
    'now' => time(),
    'count' => count($results),
    'results' => $results
  );
  
}