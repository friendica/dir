#!/usr/bin/env php
<?php

include_once dirname(__DIR__) . '/boot.php';

(new Friendica\Directory\Core\Console($argv))->execute();
