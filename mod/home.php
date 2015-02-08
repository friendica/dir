<?php

use Friendica\Directory\Rendering\View;
use Friendica\Directory\Helper\Profile as ProfileHelper;

if(! function_exists('home_content')) {
function home_content(&$a) {
    
    $profiles = q("SELECT * FROM profile WHERE comm=1 AND LENGTH(pdesc)>0 ORDER BY RAND() LIMIT 3");
    
    $view = new View('homepage', 'minimal');
    $view->addHelper('photoUrl', ProfileHelper::get('photoUrl'));
    $view->output(array(
        'profiles' => $profiles
    ));
    
}}
