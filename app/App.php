<?php

namespace App;

use App\Router\Router;

class App
{
    protected $db;
    protected $router;

    public function __construct($db)
    {
        $this->db = $db;
        $this->router = Router::getInstance();
    }

    public function run()
    {
        try {
            $this->loadRoutes();

            $method = $_SERVER['REQUEST_METHOD'];
            $uri = $this->getRequestUri();

            $this->router->route($method, $uri, $this->db);
        } catch (\Exception $e) {
            \App\View\View::renderError($e->getMessage());
        }
    }

    protected function loadRoutes()
    {
        require __DIR__ . '/Router/Routes/web.php';
    }

    protected function loadApiRoutes()
    {
        require __DIR__ . '/Router/Routes/api.php';
    }

    protected function getRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        return strtok($uri, '?');
    }
}
