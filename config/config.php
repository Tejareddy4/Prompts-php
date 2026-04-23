<?php

declare(strict_types=1);

return [
    'app' => [
        'name'         => $_ENV['APP_NAME']    ?? 'PromptShare',
        'base_url'     => $_ENV['APP_URL']     ?? 'http://localhost',
        'env'          => $_ENV['APP_ENV']     ?? 'development',
        'session_name' => $_ENV['SESSION_NAME'] ?? 'promptshare_session',
    ],
    'db' => [
        'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
        'port'     => (int)($_ENV['DB_PORT'] ?? 3306),
        'database' => $_ENV['DB_DATABASE'] ?? 'promptshare',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset'  => 'utf8mb4',
    ],
    'upload' => [
        'dir'           => __DIR__ . '/../public/assets/uploads',
        'max_size'      => 5 * 1024 * 1024,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
    'cache' => [
        'enabled' => true,
        'path'    => __DIR__ . '/../storage/cache',
        'ttl'     => 60,
    ],
    'google_oauth' => [
        'enabled'      => false,
        'client_id'    => $_ENV['GOOGLE_CLIENT_ID']     ?? '',
        'client_secret'=> $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/auth/google/callback',
    ],
];
