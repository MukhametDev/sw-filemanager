<?php

use App\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

// Настройка заголовков для CORS и безопасности
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Установка глобального обработчика исключений
set_exception_handler(function ($exception) {
    // Централизованная обработка ошибок с использованием Response
    Response::error($exception->getMessage(), 500);
});

try {
    // Загрузка и запуск приложения
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->run();
} catch (\Exception $e) {
    // Обработка ошибок с выводом ответа об ошибке через Response
    Response::error($e->getMessage(), 500);
}
