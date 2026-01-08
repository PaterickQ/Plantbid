<?php

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;

$router = new Router();
$routes = require __DIR__ . '/../config/routes.php';

foreach ($routes as $route) {
    [$method, $path, $handler] = $route;
    $router->add($method, $path, $handler);
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
