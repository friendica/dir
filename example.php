<?php

//Add the auto loader. This makes sure that we can find the files we need for a class.
require_once('autoload.php');

//This says, we want Hello to mean Friendica\Directory\Example\Hello.
//It's a shortcut.
use Friendica\Directory\Example\Hello;

//Here we use the shortcut and create a new Hello object.
$instance = new Hello();

//Let the Hello object call 
echo $instance->sayHello();
