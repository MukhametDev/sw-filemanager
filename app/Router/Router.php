<?php

namespace App\Router;

use App\Traits\Singleton;

require __DIR__ . '/../helpers/helper.php';


class Router
{
    use Singleton;

    private static $router;

    public function __construct(private array $routes = [])
    {
    }

    public function getRouter(): self
    {
        if (!isset(self::$router)) {
            self::$router = new self();
        }

        return self::$router;
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

    protected function register(string $uri, string $action, string $method)
    {
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $uri);
        $pattern = '#^' . $pattern . '$#';

        list($controller, $function) = $this->extractAction($action);

        $this->routes[$method][$pattern] = [
            'controller' => $controller,
            'method' => $function
        ];
    }

    protected function extractAction(string $action, string $seperator = '@'): array
    {

        $sepIdx = strpos($action, $seperator);

        $controller = substr($action, 0, $sepIdx);
        $function = substr($action, $sepIdx + 1, strlen($action));

        return [$controller, $function];
    }

    public function route(string $method, string $uri, $db): bool
    {
        file_put_contents('log.txt', "Routing: $method $uri\n", FILE_APPEND);

        foreach ($this->routes[$method] as $pattern => $result) {
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                $controller = $result['controller'];
                $function = $result['method'];

                file_put_contents('log.txt', "Controller: $controller, Method: $function\n", FILE_APPEND);

                if (class_exists($controller)) {
                    $controllerInstance = new $controller();  // Передаем $db в контроллер

                    if (method_exists($controllerInstance, $function)) {
                        call_user_func_array([$controllerInstance, $function], $matches);
                        return true;
                    } else {
                        \App\View\View::renderError("No method {$function} on class {$controller}");
                    }
                }
            }
        }

        \App\View\View::renderError("Route not found");
        return false;
    }
}
