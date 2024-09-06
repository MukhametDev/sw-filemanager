<?php

$router = \App\Router\Router::getInstance();

// Определение маршрутов для API-запросов
$router->post('/api/add-folder', '\App\Controllers\Api\FolderController@add');
