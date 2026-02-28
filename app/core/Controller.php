<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $config = $this->config;
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/main.php';
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
