<?php

require_once('autoload.php');

use Friendica\Directory\Example\Hello;

$instance = new Hello();
echo $instance->sayHello();
