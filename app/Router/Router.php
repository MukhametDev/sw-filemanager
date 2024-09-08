<?php

namespace App\Router;

use App\Http\Container;
use App\Traits\Singleton;
use App\Http\Response;

class Router
{
    use Singleton;

    private $routes = [];
    private Container $container;

    public function __construct()
    {
        $this->container = new Container(); // Инициализация контейнера
    }

    public function get(string $uri, string $action): void
    {
        $this->register($uri, $action, "GET");
    }

    public function post(string $uri, string $action): void
    {
        $this->register($uri, $action, "POST");
    }

    public function put(string $uri, string $action): void
    {
        $this->register($uri, $action, "PUT");
    }

    public function delete(string $uri, string $action): void
    {
        $this->register($uri, $action, "DELETE");
    }

    protected function register(string $uri, string $action, string $method): void
    {
        $pattern = '#^' . preg_replace('/\{[^\}]+\}/', '([^/]+)', $uri) . '$#';
        list($controller, $function) = $this->extractAction($action);

        $this->routes[$method][$pattern] = [
            'controller' => $controller,
            'method' => $function
        ];
    }

    protected function extractAction(string $action, string $separator = '@'): array
    {
        [$controller, $method] = explode($separator, $action);
        return [trim($controller), trim($method)];
    }

    public function route(string $method, string $uri): bool
    {
        foreach ($this->routes[$method] ?? [] as $pattern => $result) {
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $controller = $result['controller'];
                $method = $result['method'];

                try {
                    // Используем контейнер для создания экземпляра контроллера с инъекцией зависимостей
                    $controllerInstance = $this->container->get($controller);

                    if (method_exists($controllerInstance, $method)) {
                        call_user_func_array([$controllerInstance, $method], $matches);
                        return true;
                    } else {
                        Response::error("Метод {$method} не найден в классе {$controller}", 404);
                    }
                } catch (\Exception $e) {
                    Response::error($e->getMessage(), 500);
                }
            }
        }

        // Возвращаем false, если маршрут не был найден
        return false;
    }
}
