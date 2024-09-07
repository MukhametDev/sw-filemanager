<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    $app->run();
} catch (\Exception $e) {
    \App\View\View::renderError($e->getMessage());
}
