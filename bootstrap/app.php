<?php

use App\Config\Config;
use App\DB\Database;
use App\App;
use App\Http\Container;
use App\Services\DirectoryService;
use App\Services\FileService;
use App\Interfaces\DirectoryServiceInterface;
use App\Interfaces\FileServiceInterface;
use App\Interfaces\DirectoryRepositoryInterface;
use App\Interfaces\FileRepositoryInterface;
use App\Repository\DirectoryRepository;
use App\Repository\FileRepository;
use App\Http\Response;
use App\Interfaces\ResponseInterface;
use App\Services\BuildTreeService;
use App\Interfaces\BuildTreeInterface;

require_once __DIR__ . '/../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

set_exception_handler(function ($exception) {
    Response::error($exception->getMessage(), 500);
});

$config = Config::getInstance();

$db = Database::getInstance($config->getDBSettings());
$db->init();

$container = new Container();

$container->bind(DirectoryServiceInterface::class, DirectoryService::class);
$container->bind(FileServiceInterface::class, FileService::class);
$container->bind(DirectoryRepositoryInterface::class, DirectoryRepository::class);
$container->bind(FileRepositoryInterface::class, FileRepository::class);
$container->bind(ResponseInterface::class, Response::class);
$container->bind(BuildTreeInterface::class, BuildTreeService::class);

$app = new App($db, $container);

return $app;