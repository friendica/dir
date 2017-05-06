<?php
use Friendica\Directory\Rendering\View;

if(! function_exists('help_content')) {
    function help_content(&$a) {
        $view = new View('help');
        $view->output();
    }
}

