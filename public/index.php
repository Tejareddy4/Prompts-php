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

// ── Base path — lets the app run at the domain root OR in a subfolder
//    (e.g. http://localhost/prompts). Auto-detected, so production is untouched.
$docRoot   = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$publicDir = str_replace('\\', '/', __DIR__); // .../<project>/public
$basePath  = '';
if ($docRoot !== '' && str_starts_with($publicDir, $docRoot)) {
    $basePath = substr($publicDir, strlen($docRoot)); // '/prompts/public' | '/public' | ''
}
if (str_ends_with($basePath, '/public')) {
    $basePath = substr($basePath, 0, -strlen('/public'));
}
$basePath = '/' . trim($basePath, '/');
if ($basePath === '/') { $basePath = ''; }
define('BASE_PATH', $basePath);

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
if (BASE_PATH !== '' && str_starts_with($uri, BASE_PATH)) {
    $uri = substr($uri, strlen(BASE_PATH));
}
$uri = $uri === '' ? '/' : $uri;

// Buffer the response so that, when running in a subfolder, root-relative
// links (href/src/action="/…") can be prefixed with the base path. In a
// root deploy BASE_PATH is '' and the output passes through untouched.
ob_start();
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
$html = ob_get_clean();
if (BASE_PATH !== '') {
    $html = preg_replace(
        '#(\s(?:href|src|action)=)(["\'])/(?!/)#i',
        '$1$2' . BASE_PATH . '/',
        $html
    );
}
echo $html;
