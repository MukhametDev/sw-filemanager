<?php

use App\Config\Config;
use App\DB\Database;
use App\App;

// Подключаем автозагрузчик классов Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Загружаем конфигурацию приложения
$config = Config::getInstance();

// Инициализируем базу данных с конфигурацией
$db = Database::getInstance($config->getDBSettings());
$db->init();

// Создаем и возвращаем экземпляр приложения
$app = new App($db);

return $app;
