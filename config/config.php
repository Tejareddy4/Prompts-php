<?php

declare(strict_types=1);

// Load .env if phpdotenv is installed
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    if (class_exists('\Dotenv\Dotenv')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
    }
}

return [
    'app' => [
        'name'         => $_ENV['APP_NAME']     ?? 'PromptShare',
        'base_url'     => $_ENV['APP_URL']       ?? 'https://prompt.xpanda.in',
        'env'          => $_ENV['APP_ENV']       ?? 'production',
        'session_name' => 'promptshare_session',
    ],
    'db' => [
        'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
        'port'     => (int)($_ENV['DB_PORT'] ?? 3306),
        'database' => $_ENV['DB_DATABASE'] ?? 'u166258402_promptshare',
        'username' => $_ENV['DB_USERNAME'] ?? 'u166258402_promptshare',
        'password' => $_ENV['DB_PASSWORD'] ?? '',   // ← set in .env, never hardcode
        'charset'  => 'utf8mb4',
    ],
    'upload' => [
        'dir'           => __DIR__ . '/../public/assets/uploads',
        'max_size'      => 5 * 1024 * 1024,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
    'cache' => [
        'enabled' => filter_var($_ENV['CACHE_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'path'    => __DIR__ . '/../storage/cache',
        'ttl'     => (int)($_ENV['CACHE_TTL'] ?? 60),
    ],
    'google_oauth' => [
        'enabled'      => filter_var($_ENV['GOOGLE_OAUTH_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'client_id'    => $_ENV['GOOGLE_CLIENT_ID']     ?? '',
        'client_secret'=> $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI']  ?? 'https://prompt.xpanda.in/auth/google/callback',
    ],
];
