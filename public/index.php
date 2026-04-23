<?php

declare(strict_types=1);

use App\Core\Router;

require __DIR__ . '/../app/core/helpers.php';

session_name(config('app.session_name') ?? 'promptshare');
session_start();

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $segments = explode('/', $relative);
    $segments[0] = strtolower($segments[0]);
    $path = __DIR__ . '/../app/' . implode('/', $segments) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

// Ensure required directories exist
$cfg = config();
foreach ([
    $cfg['upload']['dir'] ?? null,
    $cfg['cache']['path'] ?? null,
] as $dir) {
    if ($dir && !is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

$router = new Router();
require __DIR__ . '/../routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$uri = $uri === '' ? '/' : $uri;

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
