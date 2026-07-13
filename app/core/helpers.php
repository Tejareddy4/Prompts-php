<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;

function config(?string $key = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../../config/config.php';
    }

    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }

    return $value;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Prefix a root-relative app path with the base path (for subfolder installs). */
function app_url(string $path): string
{
    // Leave empty, relative, protocol-relative, or absolute URLs untouched.
    if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
        return $path;
    }
    return (defined('BASE_PATH') ? BASE_PATH : '') . $path;
}

/** Asset URL with a file-mtime version param, so long-lived browser caches bust on change. */
function asset(string $path): string
{
    $file = dirname(__DIR__, 2) . '/public' . $path;
    $v = is_file($file) ? filemtime($file) : null;
    return $path . ($v ? '?v=' . $v : '');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . Csrf::token() . '">';
}

function auth_user(): ?array
{
    return Auth::user();
}

// ── Logging shortcuts (channels: storage/logs/app-*.log + error-*.log) ──
function log_debug(string $message, array $context = []): void   { \App\Core\Logger::debug($message, $context); }
function log_info(string $message, array $context = []): void    { \App\Core\Logger::info($message, $context); }
function log_warning(string $message, array $context = []): void { \App\Core\Logger::warning($message, $context); }
function log_error(string $message, array $context = []): void   { \App\Core\Logger::error($message, $context); }

function is_liked(array $prompt): bool
{
    return (bool)($prompt['is_liked'] ?? false);
}

function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    // Support old string-only flashes for backwards compat
    if (is_string($flash)) {
        return ['message' => $flash, 'type' => 'info'];
    }
    return $flash;
}

/** All categories, queried once per request — for the footer nav and other chrome. */
function all_categories(): array
{
    static $cats = null;
    if ($cats !== null) {
        return $cats;
    }
    try {
        $db = \App\Core\Database::connection(config('db'));
        $cats = (new \App\Models\Category($db))->all();
    } catch (\Throwable $e) {
        \App\Core\Logger::exception($e, 'all_categories failed');
        $cats = [];
    }
    return $cats;
}

/** Renders a small "cat-{color}" badge for a row carrying category_name/slug/icon/color. */
function category_badge(array $item, string $size = 'sm'): string
{
    if (empty($item['category_slug'])) {
        return '';
    }
    $cls = $size === 'sm' ? 'cat-badge cat-badge-sm' : 'cat-badge';
    return '<span class="' . $cls . ' cat-' . e($item['category_color']) . '">'
         . '<i class="bi ' . e($item['category_icon']) . '"></i> ' . e($item['category_name'])
         . '</span>';
}
