<?php
$db = require __DIR__ . '/db.php';
$db['dsn'] = 'mysql:host=chem-backend-yii2-db-1;port=3306;dbname=chem-backend-yii2-test';

return $db;
