<?php
// One-time deploy script — DELETE after use
// Visit: https://prompt.xpanda.in/deploy.php?key=deploy2024ps

if (($_GET['key'] ?? '') !== 'deploy2024ps') {
    http_response_code(403); die('Forbidden');
}

echo "<pre style='font-family:monospace;font-size:13px;padding:20px;'>\n";
echo "=== PromptShare Deploy ===\n\n";

$root = dirname(__DIR__);
echo "Root: $root\n\n";

// Clear cache files
$cacheDir = $root . '/storage/cache';
$cleared = 0;
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.phpcache') as $f) {
        if (unlink($f)) $cleared++;
    }
}
echo "Cache cleared: $cleared files\n\n";

// Git pull
echo "Running git pull...\n";
$output = shell_exec("cd $root && git pull origin main 2>&1");
echo $output ? $output : "No output (shell_exec may be disabled)\n";

echo "\n=== Done ===\n";
echo "DELETE this file now for security!\n";
echo "</pre>";
