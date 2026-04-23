<?php

declare(strict_types=1);

use App\Core\Router;

// ── Load .env ────────────────────────────────────────────────
$envFile = __DIR__ . '/../.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        $val = preg_replace('/^(["\'])(.*)\\1$/', '$2', $val);
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}

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
