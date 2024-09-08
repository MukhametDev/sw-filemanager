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

require_once __DIR__ . '/../vendor/autoload.php';

// Инициализация конфигурации
$config = Config::getInstance();

// Инициализация базы данных
$db = Database::getInstance($config->getDBSettings());
$db->init();

// Инициализация контейнера зависимостей
$container = new Container();

// Настройка привязок интерфейсов к их реализациям
$container->bind(DirectoryServiceInterface::class, DirectoryService::class);
$container->bind(FileServiceInterface::class, FileService::class);
$container->bind(DirectoryRepositoryInterface::class, DirectoryRepository::class);
$container->bind(FileRepositoryInterface::class, FileRepository::class);
$container->bind(ResponseInterface::class, Response::class);

// Создание экземпляра приложения с контейнером
$app = new App($db, $container);

// Возвращаем экземпляр приложения
return $app;
