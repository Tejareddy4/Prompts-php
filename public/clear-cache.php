<?php
// Emergency cache clear — DELETE THIS FILE AFTER USE
// Access via: https://prompt.xpanda.in/clear-cache.php

$secret = $_GET['key'] ?? '';
if ($secret !== 'promptshare2024clear') {
    http_response_code(403);
    die('Forbidden');
}

$cacheDir = __DIR__ . '/../storage/cache';
$deleted  = 0;
$errors   = 0;

if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.phpcache') as $file) {
        if (unlink($file)) {
            $deleted++;
        } else {
            $errors++;
        }
    }
}

echo "<pre>Cache cleared.\nDeleted: $deleted files\nErrors: $errors\nDone. Delete this file now.</pre>";
