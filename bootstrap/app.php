<?php

use App\Config\Config;
use App\DB\Database;
use App\App;

require_once __DIR__ . '/../vendor/autoload.php';

$config = Config::getInstance();

$db = Database::getInstance($config->getDBSettings());
$db->init();

$app = new App($db);

return $app;
