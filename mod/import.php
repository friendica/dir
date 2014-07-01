<?php

function import_init(&$a)
{
  
  if(! $_SESSION['uid']) {
    notice("Permission denied.");
    goaway($a->get_baseurl());
  }
  
}

function import_content(&$a)
{
  
  $tpl = file_get_contents('view/import.tpl');
  return replace_macros($tpl, array(
    '$present' => is_file('.htimport') ? ' (present)' : ''
  ));
  
}

function import_post(&$a)
{
  
  //Get our input.
  $url = $_POST['url'];
  $page = intval($_POST['page']);
  $batch = $_POST['batch_submit'];
  
  //Directory
  $file = realpath(__DIR__.'/..').'/.htimport';
  
  //Per batch setting.
  $perPage = 200;
  $perBatch = 2;
  
  if($batch){
    
    require_once('include/submit.php');
    
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
          if(run_submit($a, $url)){
            $_SESSION['import_failed']--;
            $_SESSION['import_success']++;
          }
        }catch(\Exception $ex){/* We tried... */}
      }
      else break;
    }
    
    $left = count($list);
    
    $s = $_SESSION['import_success'];
    $total = $_SESSION['import_total'];
    $errors = $_SESSION['import_failed'];
    if($left > 0){
      notice("$left items left in batch.<br>Stats: $s / $total success, $errors errors.");
      file_put_contents($file, implode("\r\n", $list));
      $fid = uniqid('autosubmit_');
      echo '<form method="POST" id="'.$fid.'"><input type="hidden" name="batch_submit" value="1"></form>'.
        '<script type="text/javascript">setTimeout(function(){ document.getElementById("'.$fid.'").submit(); }, 500);</script>';
    } else {
      notice("Completed batch! $s / $total success. $errors errors.");
      unlink($file);
      unset($_SESSION['import_progress']);
    }
    
    return;
    
  }
  elseif($url && $page){
    
    $result = fetch_url($url."/lsearch?p=$page&n=$perPage&search=.*");
    if($result)
      $data = json_decode($result);
    else
      $data = false;
    
    if($data){
      
      $rows = '';
      foreach($data->results as $profile){
        $rows .= $profile->url."\r\n";
      }
      file_put_contents($file, $rows, $page > 0 ? FILE_APPEND : 0);
      
      $progress = min((($page+1) * $perPage), $data->total);
      notice("Imported ".$progress."/".$data->total." URLs.");
      
      if($progress !== $data->total){
        $fid = uniqid('autosubmit_');
        echo
          '<form method="POST" id="'.$fid.'">'.
            '<input type="hidden" name="url" value="'.$url.'">'.
            '<input type="hidden" name="page" value="'.($page+1).'">'.
            // '<input type="submit">'.
          '</form>'.
          '<script type="text/javascript">setTimeout(function(){ document.getElementById("'.$fid.'").submit(); }, 500);</script>';
        
      } else {
        goaway($a->get_baseurl().'/import');
      }
      
    }
    
  }
  
}