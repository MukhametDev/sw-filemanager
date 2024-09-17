<?php

use App\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->run();
} catch (\Exception $e) {
    Response::error($e->getMessage(), 500);
}