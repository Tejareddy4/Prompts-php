<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    private function addRoute(string $method, string $pattern, callable|array $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', '(?P<$1>[^/]+)', $route['pattern']) . '$#';
            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                if (!Auth::checkMiddleware($middleware)) {
                    return;
                }
            }

            $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);

            if (is_callable($route['handler'])) {
                call_user_func($route['handler'], $params);
                return;
            }

            [$controller, $action] = $route['handler'];
            $instance = new $controller(config());
            $instance->$action($params);
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
