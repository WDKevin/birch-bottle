<?php
// Change to current directory
chdir(realpath(__DIR__));

// Init autoloading
require_once('vendor/autoload.php');

// Prevent PHP from stopping the script after 30 sec
set_time_limit(0);

// Include config options
$config = require_once('config.php');

$bot = new BirchBottle\Bot($config['server'], $config['port'], $config['nick'], $config['channel']);


$bot->run();
  