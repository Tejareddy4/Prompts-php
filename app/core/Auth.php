<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = $user;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role_name'] ?? '') === 'super_admin';
    }

    public static function checkMiddleware(string $middleware): bool
    {
        if ($middleware === 'auth' && !self::check()) {
            header('Location: /login');
            return false;
        }

        if ($middleware === 'admin' && !self::isAdmin()) {
            http_response_code(403);
            echo '403 Forbidden';
            return false;
        }

        return true;
    }
}
