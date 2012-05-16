<?php

// Set the following for your MySQL installation
// Copy or rename this file to .htconfig.php

$db_host = '$dbhost';
$db_user = '$dbuser';
$db_pass = '$dbpass';
$db_data = '$dbdata';

// If you are using a subdirectory of your domain you will need to put the
// relative path (from the root of your domain) here.
// For instance if your URL is 'http://example.com/directory/subdirectory',
// set $a->path to 'directory/subdirectory'.

$a->path = '';


// Choose a legal default timezone. If you are unsure, use "America/Los_Angeles".
// It can be changed later and only applies to timestamps for anonymous viewers.

$default_timezone = '$timezone';

// What is your site name?

$a->config['sitename'] = "My Friend Network";

// Maximum size of an imported message, 0 is unlimited (but our database 'text' element is limited to 65535).

$a->config['max_import_size'] = 65535;

// Location of PHP command line processor

$a->config['php_path'] = '$phpath';
