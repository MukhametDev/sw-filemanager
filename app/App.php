<?php

namespace App;

use App\Router\Router;
use App\Http\Response;

class App
{
    protected $db;
    protected $router;

    public function __construct($db)
    {
        $this->db = $db;
        $this->router = Router::getInstance();
    }

    public function run(): void
    {
        try {
            $this->loadRoutes();
            $method = $_SERVER['REQUEST_METHOD'];
            $uri = $this->getRequestUri();

            // Запуск маршрутизации с использованием DI и обработки ошибок через Response
            if (!$this->router->route($method, $uri)) {
                // Если маршрут не найден, возвращаем ответ с ошибкой
                Response::error('Маршрут не найден', 404);
            }
        } catch (\Exception $e) {
            // Обработка исключений с выводом ответа об ошибке через Response
            Response::error($e->getMessage(), 500);
        }
    }

    protected function loadRoutes(): void
    {
        require __DIR__ . '/Router/Routes/web.php';
    }

    protected function getRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        return strtok($uri, '?');
    }
}
