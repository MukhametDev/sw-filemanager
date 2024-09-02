<?php

use App\App;
use App\DB\Database;

require __DIR__ . '/../vendor/autoload.php';

try {
    $db = Database::getInstance();
    $db->init();
    $connection = $db->getConnection(); // Получаем подключение

    // Выполнение простого запроса
    $stmt = $connection->query('SELECT VERSION()');
    $version = $stmt->fetchColumn();
    // $db = Database::getInstance(); // Получение Singleton экземпляра
    // $app = new App($db);
    // $connection = $app->run();
    dd($connection);
} catch (\Exception $e) {
    echo $e->getMessage();
}
