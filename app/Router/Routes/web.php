<?php

$router = \App\Router\Router::getInstance();

$router->get('/api/add', '\App\Controllers\Api\FileController@say');
$router->get('/', '\App\Controllers\HomeController@index');
$router->post('/api/add-folder', '\App\Controllers\Api\FolderController@add');
$router->get('/api/get-directories', '\App\Controllers\Api\FolderController@getDirectories');
$router->post('/api/upload-file', '\App\Controllers\Api\FileController@uploadFile');
$router->get('/api/download-file', '\App\Controllers\Api\FileController@downloadFile');
$router->delete('/api/delete-file', '\App\Controllers\Api\FileController@deleteFile');
$router->delete('/api/delete-folder', '\App\Controllers\Api\FolderController@deleteFolder');
$router->get('/uploads/show', '\App\Controllers\Api\FileController@showImage');
