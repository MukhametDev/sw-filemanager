<?php

header("Access-Control-Allow-Origin: *"); // или укажите конкретный домен
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Подключаем bootstrap файл, чтобы получить приложение
$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    // Запуск приложения
    $app->run();
} catch (\Exception $e) {
    // Обработка исключений и рендеринг ошибки
    \App\View\View::renderError($e->getMessage());
}
