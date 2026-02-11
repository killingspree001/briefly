<?php
/**
 * Briefly.ai — Local Development Router
 * 
 * This replicates Vercel's routing for PHP's built-in server.
 * Usage: php -S localhost:8000 router.php
 * 
 * Routes:
 *   /styles.css, /app.js  → public/
 *   /api/articles          → api/articles.php
 *   /api/fetch             → api/fetch.php
 *   /api/process           → api/process.php
 *   /api/cron              → api/cron.php
 *   /api/pdf               → api/pdf.php
 *   /api/summarize         → (Python — run separately)
 *   Everything else        → api/index.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ─── Static files from public/ ───────────────────────────────────────
$staticFile = __DIR__ . '/public' . $uri;
if (is_file($staticFile)) {
    $ext = pathinfo($staticFile, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($staticFile);
    return true;
}

// ─── API routes ──────────────────────────────────────────────────────
$apiRoutes = [
    '/api/articles' => '/api/articles.php',
    '/api/fetch'    => '/api/fetch.php',
    '/api/process'  => '/api/process.php',
    '/api/cron'     => '/api/cron.php',
    '/api/pdf'      => '/api/pdf.php',
    '/archive'      => '/api/archive.php',
];

if (isset($apiRoutes[$uri])) {
    require __DIR__ . $apiRoutes[$uri];
    return true;
}

// ─── Catch-all → main page ──────────────────────────────────────────
require __DIR__ . '/api/index.php';
return true;
