<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'PromptShare',
        'base_url' => 'http://localhost',
        'env' => 'development',
        'session_name' => 'promptshare_session',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'promptshare',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'upload' => [
        'dir' => __DIR__ . '/../public/assets/uploads',
        'max_size' => 5 * 1024 * 1024,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
    'cache' => [
        'enabled' => true,
        'path' => __DIR__ . '/../storage/cache',
        'ttl' => 60,
    ],
    'google_oauth' => [
        'enabled' => false,
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => 'http://localhost/auth/google/callback',
    ],
];
