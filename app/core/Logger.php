<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File logger with two channels:
 *   app-YYYY-MM-DD.log   — everything at/above the configured level
 *   error-YYYY-MM-DD.log — warning + error only (the "where it breaks" file)
 *
 * Never throws: if the log dir is unwritable it falls back to PHP's error_log.
 */
class Logger
{
    private const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

    private static string $path = '';
    private static int $minLevel = 1;

    public static function init(array $config): void
    {
        self::$path = rtrim($config['path'] ?? '', '/\\');
        self::$minLevel = self::LEVELS[$config['level'] ?? 'info'] ?? 1;

        if (self::$path !== '' && !is_dir(self::$path)) {
            @mkdir(self::$path, 0755, true);
        }
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /** Log a throwable with file:line and a compact stack trace. */
    public static function exception(\Throwable $e, string $note = ''): void
    {
        self::error(
            ($note !== '' ? $note . ': ' : '') . get_class($e) . ': ' . $e->getMessage(),
            [
                'at'    => $e->getFile() . ':' . $e->getLine(),
                'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 8),
            ]
        );
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        if (self::$path === '' || (self::LEVELS[$level] ?? 1) < self::$minLevel) {
            return;
        }

        $line = sprintf(
            "[%s] %s: %s%s | %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '',
            self::requestInfo(),
            self::userInfo()
        );

        $date = date('Y-m-d');
        $ok = @file_put_contents(self::$path . "/app-{$date}.log", $line, FILE_APPEND | LOCK_EX);
        if ((self::LEVELS[$level] ?? 1) >= self::LEVELS['warning']) {
            @file_put_contents(self::$path . "/error-{$date}.log", $line, FILE_APPEND | LOCK_EX);
        }
        if ($ok === false) {
            error_log('[promptshare] ' . rtrim($line));
        }
    }

    /** Log files available for the admin viewer, newest first. */
    public static function files(): array
    {
        if (self::$path === '' || !is_dir(self::$path)) {
            return [];
        }
        $files = array_merge(
            glob(self::$path . '/app-*.log') ?: [],
            glob(self::$path . '/error-*.log') ?: []
        );
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
        return array_map('basename', $files);
    }

    /** Last $limit lines of a log file, newest first. Name must be a bare file name. */
    public static function tail(string $file, int $limit = 200): array
    {
        if (!preg_match('/^(app|error)-\d{4}-\d{2}-\d{2}\.log$/', $file)) {
            return [];
        }
        $full = self::$path . '/' . $file;
        if (!is_file($full)) {
            return [];
        }
        $lines = file($full, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        return array_reverse(array_slice($lines, -$limit));
    }

    /** Delete one log file (admin "clear" action). Name must be a bare file name. */
    public static function clear(string $file): bool
    {
        if (!preg_match('/^(app|error)-\d{4}-\d{2}-\d{2}\.log$/', $file)) {
            return false;
        }
        $full = self::$path . '/' . $file;
        return is_file($full) && @unlink($full);
    }

    private static function requestInfo(): string
    {
        if (PHP_SAPI === 'cli') {
            return 'cli';
        }
        return ($_SERVER['REQUEST_METHOD'] ?? '?') . ' ' . ($_SERVER['REQUEST_URI'] ?? '?')
             . ' | ip:' . ($_SERVER['REMOTE_ADDR'] ?? '?');
    }

    private static function userInfo(): string
    {
        $id = $_SESSION['user']['id'] ?? null;
        return $id ? ' | user:' . $id : '';
    }
}
