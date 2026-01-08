<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string $handler): void
    {
        $method = strtoupper($method);
        $path = '/' . ltrim($path, '/');
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->resolvePath($uri);

        $handler = $this->routes[$method][$path] ?? null;
        if ($handler === null) {
            http_response_code(404);
            $this->renderNotFound();
            return;
        }

        [$controller, $action] = explode('@', $handler, 2);
        $controllerInstance = new $controller();
        $controllerInstance->$action();
    }

    private function resolvePath(string $uri): string
    {
        if (APP_CONFIG['use_query_routes']) {
            $route = $_GET['route'] ?? '';
            $route = '/' . ltrim($route, '/');
            return $route === '/' ? '/' : rtrim($route, '/');
        }

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . ltrim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function renderNotFound(): void
    {
        $view = __DIR__ . '/../Views/errors/404.php';
        if (is_file($view)) {
            require $view;
            return;
        }
        echo 'Not found';
    }
}
