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

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . Csrf::token() . '">';
}

function auth_user(): ?array
{
    return Auth::user();
}

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
