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
