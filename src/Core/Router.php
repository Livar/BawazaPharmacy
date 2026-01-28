<?php

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $route, array $handler): void
    {
        $this->routes['GET'][$route] = $handler;
    }

    public function post(string $route, array $handler): void
    {
        $this->routes['POST'][$route] = $handler;
    }

    public function dispatch(string $route, string $method): void
    {
        $handler = $this->routes[$method][$route] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }

        [$class, $action] = $handler;
        $controller = new $class();
        $controller->$action();
    }
}
