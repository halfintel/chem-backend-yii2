<?php

$config = require __DIR__ . '/web.php';
$config['components']['db'] = require __DIR__ . '/test_db.php';
$config['params'] = require __DIR__ . '/test_params.php';

return $config;