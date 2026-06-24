<?php

/**
 * Vercel PHP runtime entry point for Laravel.
 *
 * All HTTP requests are routed here by vercel.json.
 * Static assets (css, js, images, lib) are served directly via vercel.json routes.
 */

$root = dirname(__DIR__);

// Vercel's filesystem is read-only except for /tmp.
// Create writable framework directories so Laravel can compile views and write logs.
if (!empty($_ENV['VERCEL'])) {
    $dirs = [
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/logs',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

$_SERVER['DOCUMENT_ROOT']   = $root . '/public';
$_SERVER['SCRIPT_FILENAME'] = $root . '/public/index.php';

require $root . '/public/index.php';
