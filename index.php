<?php
/**
 * Root entry point — handles Hostinger shared hosting where
 * the document root points to the project root, not public/.
 * Simply forwards everything to public/index.php.
 */

// Fix paths so public/index.php resolves all relative paths correctly
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';

// Run the real entry point
require __DIR__ . '/public/index.php';
