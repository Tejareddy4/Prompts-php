<?php

declare(strict_types=1);

namespace App\Core;

class Cache
{
    public function __construct(private array $config)
    {
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!$this->config['enabled']) {
            return $callback();
        }

        $file = $this->config['path'] . '/' . md5($key) . '.phpcache';
        $ttl ??= $this->config['ttl'];

        if (is_file($file) && (time() - filemtime($file) < $ttl)) {
            return unserialize((string) file_get_contents($file));
        }

        $value = $callback();
        file_put_contents($file, serialize($value));

        return $value;
    }

    public function forget(string $key): void
    {
        $file = $this->config['path'] . '/' . md5($key) . '.phpcache';
        if (is_file($file)) {
            unlink($file);
        }
    }
}
