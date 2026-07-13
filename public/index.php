<?php

declare(strict_types=1);

use App\Core\Router;

// PHP built-in dev server (php -S … public/index.php): serve real files directly
if (PHP_SAPI === 'cli-server') {
    $devPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    if ($devPath !== '/' && is_file(__DIR__ . $devPath)) {
        return false;
    }
}

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
    $cfg['log']['path'] ?? null,
] as $dir) {
    if ($dir && !is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ── Logging + global error handlers ──────────────────────────
\App\Core\Logger::init($cfg['log']);

set_error_handler(function (int $no, string $msg, string $file, int $line): bool {
    if (!(error_reporting() & $no)) {
        return false; // suppressed with @
    }
    $level = in_array($no, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)
        ? 'info' : 'warning';
    \App\Core\Logger::log($level, "PHP: $msg", ['at' => "$file:$line"]);
    return false; // keep PHP's default behaviour (display_errors in dev)
});

register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        \App\Core\Logger::error('FATAL: ' . $err['message'], ['at' => $err['file'] . ':' . $err['line']]);
    }
});

/** Discard any partial output and show the styled 500 page. */
function render_error_page(?Throwable $e = null): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    $detail = (config('app.env') === 'local' && $e) ? $e : null;
    require __DIR__ . '/../app/views/errors/500.php';
}

set_exception_handler(function (Throwable $e): void {
    \App\Core\Logger::exception($e, 'Uncaught');
    render_error_page($e);
});

$requestStart = microtime(true);

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
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
} catch (Throwable $e) {
    \App\Core\Logger::exception($e, 'Unhandled');
    render_error_page($e);
    exit;
}
$elapsedMs = (int) round((microtime(true) - $requestStart) * 1000);
if ($elapsedMs > (int) ($cfg['log']['slow_ms'] ?? 1500)) {
    \App\Core\Logger::warning("Slow request: {$elapsedMs}ms");
}
$html = ob_get_clean();
if (BASE_PATH !== '') {
    $html = preg_replace(
        '#(\s(?:href|src|action)=)(["\'])/(?!/)#i',
        '$1$2' . BASE_PATH . '/',
        $html
    );
}
echo $html;
