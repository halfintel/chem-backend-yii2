<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

const CONSOLE_TEST = true;

$config = require __DIR__ . '/console.php';
$config['components']['db'] = require __DIR__ . '/test_db.php';
$config['params'] = require __DIR__ . '/test_params.php';


return $config;
