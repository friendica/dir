<?php


function admin_content(&$a) {
	
	if(! $_SESSION['uid']) {
		notice("Permission denied.");
		goaway($a->get_baseurl());
	}
	
	//Get 100 flagged entries.
	$r = q("SELECT `flag`.*, `profile`.`name`, `profile`.`homepage`
					FROM `flag` JOIN `profile` ON `flag`.`pid`=`profile`.`id`
					ORDER BY `total` DESC LIMIT 100"
				);
	
	if(count($r)) {
		$flagged = '';
		foreach($r as $rr) {
			if($rr['reason'] == 1)
				$str = 'Adult';
			if($rr['reason'] == 2)
				$str = 'Dead';
			$flagged .=  '<a href="' . 'moderate/' . $rr['pid'] . '/' . $rr['reason'] . '">'.
				"{$rr['total']}x $str - [#{$rr['pid']}] {$rr['name']} ({$rr['homepage']})</a><br />";
		}
	} else {
		$flagged = 'No entries.';
	}
	
  //Get the maintenance backlog size.
  $res = q("SELECT count(*) as `count` FROM `profile` WHERE `updated` < '%s'",
    dbesc(date('Y-m-d H:i:s', time()-$a->config['maintenance']['min_scrape_delay'])));
  $maintenance_backlog = 'unknown';
  if(count($res)){ $maintenance_backlog = $res[0]['count'].' entries'; }
  
	//Get the pulling backlog size.
	$res = q("SELECT count(*) as `count` FROM `sync-pull-queue`");
  $pulling_backlog = 'unknown';
  if(count($res)){ $pulling_backlog = $res[0]['count'].' entries'; }
	
	$tpl = file_get_contents('view/admin.tpl');
  return replace_macros($tpl, array(
    '$present' => is_file('.htimport') ? ' (present)' : '',
    '$flagged' => $flagged,
    '$maintenance_backlog' => $maintenance_backlog,
    '$pulling_backlog' => $pulling_backlog,
    '$maintenance_size' => $a->config['maintenance']['max_scrapes'].' items per maintenance call.'
  ));
	
}

function admin_post(&$a)
{
	
	//Submit a profile URL.
  if($_POST['submit_url']){
    goaway($a->get_baseurl().'/submit?url='.bin2hex($_POST['submit_url']));
  }
  
  //Get our input.
  $url = $_POST['dir_import_url'];
  $page = intval($_POST['dir_page']);
  $batch = $_POST['batch_submit'];
  
  //Directory
  $file = realpath(__DIR__.'/..').'/.htimport';
  
  //Per batch setting.
  $perPage = 200;
  $perBatch = 2;
  
  if($batch){
    
    require_once('include/submit.php');
    require_once('include/site-health.php');
    
    //First get all data from file.
    $data = file_get_contents($file);
    $list = explode("\r\n", $data);
    
    //Fresh batch?
    if(!isset($_SESSION['import_progress'])){
      
      $_SESSION['import_progress'] = true;
      $_SESSION['import_success'] = 0;
      $_SESSION['import_failed'] = 0;
      $_SESSION['import_total'] = 0;
      notice("Started new batch. ");
      
    }
    
    //Make sure we can use try catch for all sorts of errors.
    set_error_handler(function($errno, $errstr='', $errfile='', $errline='', $context=array()){
      if((error_reporting() & $errno) == 0){ return; }
      throw new \Exception($errstr, $errno);
    });
    
    for($i=0; $i<$perBatch; $i++){
      if($url = array_shift($list)){
        set_time_limit(15);
        $_SESSION['import_total']++;
        $_SESSION['import_failed']++;
        try{
        	
        	//A site may well turn 'sour' during the import.
        	//Check the health again for this reason.
        	$site = parse_site_from_url($url);
					$r = q("SELECT * FROM `site-health` WHERE `base_url`= '%s' ORDER BY `id` ASC LIMIT 1", $site);
					if(count($r) && intval($r[0]['health_score']) < $a->config['site-health']['skip_import_threshold']){
						continue;
					}
        	
        	//Do the submit if health is ok.
          if(run_submit($url)){
            $_SESSION['import_failed']--;
            $_SESSION['import_success']++;
          }
          
        }catch(\Exception $ex){/* We tried... */}
      }
      else break;
    }
    
    $left = count($list);
    
    $success = $_SESSION['import_success'];
    $skipped = $_SESSION['import_skipped'];
    $total = $_SESSION['import_total'];
    $errors = $_SESSION['import_failed'];
    if($left > 0){
      notice("$left items left in batch...<br>$success updated profiles.<br>$errors import errors.");
      file_put_contents($file, implode("\r\n", $list));
      $fid = uniqid('autosubmit_');
      echo '<form method="POST" id="'.$fid.'"><input type="hidden" name="batch_submit" value="1"></form>'.
        '<script type="text/javascript">setTimeout(function(){ document.getElementById("'.$fid.'").submit(); }, 300);</script>';
    } else {
      notice("Completed batch! $success updated. $errors errors.");
      unlink($file);
      unset($_SESSION['import_progress']);
    }
    
    return;
    
  }
  
  //Doing a poll from the directory?
  elseif($url){
    
    require_once('include/site-health.php');
    
    $result = fetch_url($url."/lsearch?p=$page&n=$perPage&search=.*");
    if($result)
      $data = json_decode($result);
    else
      $data = false;
    
    if($data){
      
      $rows = '';
      foreach($data->results as $profile){
      	
      	//Skip known profiles.
      	$purl = $profile->url;
      	$nurl = str_replace(array('https:','//www.'), array('http:','//'), $purl);
      	$r = q("SELECT count(*) as `matched` FROM `profile` WHERE (`homepage` = '%s' OR `nurl` = '%s') LIMIT 1",
					dbesc($purl),
					dbesc($nurl)
				);
				if(count($r) && $r[0]['matched']){
					continue;
				}
				
				//Find out site health.
				else{
					
					$site = parse_site_from_url($purl);
					$r = q("SELECT * FROM `site-health` WHERE `base_url`= '%s' ORDER BY `id` ASC LIMIT 1", $site);
					if(count($r) && intval($r[0]['health_score']) < $a->config['site-health']['skip_import_threshold']){
						continue;
					}
					
				}
      	
        $rows .= $profile->url."\r\n";
        
      }
      
      file_put_contents($file, $rows, $page > 0 ? FILE_APPEND : 0);
      
      $progress = min((($page+1) * $perPage), $data->total);
      notice("Imported ".$progress."/".$data->total." URLs.");
      
      if($progress !== $data->total){
        $fid = uniqid('autosubmit_');
        echo
          '<form method="POST" id="'.$fid.'">'.
            '<input type="hidden" name="dir_import_url" value="'.$url.'">'.
            '<input type="hidden" name="dir_page" value="'.($page+1).'">'.
          '</form>'.
          '<script type="text/javascript">setTimeout(function(){ document.getElementById("'.$fid.'").submit(); }, 500);</script>';
        
      } else {
        goaway($a->get_baseurl().'/admin');
      }
      
    }
    
  }
	
}