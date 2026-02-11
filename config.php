<?php
/**
 * Briefly.ai — Global Configuration
 * 
 * All secrets are read from environment variables.
 * Locally:  loaded from .env file
 * Vercel:   set in the Vercel dashboard under "Environment Variables"
 */

// ─── Load .env file for local development ────────────────────────────
function loadEnv(string $path): void {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        if (!getenv($key)) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// Auto-load .env from project root
loadEnv(__DIR__ . '/.env');       // If config is in root
loadEnv(__DIR__ . '/../.env');    // If config is included from api/

// ─── Database ────────────────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'briefly_ai');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ─── News API ────────────────────────────────────────────────────────
define('NEWS_API_KEY', getenv('NEWS_API_KEY') ?: '');
define('NEWS_API_URL', 'https://newsapi.org/v2/top-headlines');

// ─── Summarizer URL ──────────────────────────────────────────────────
// On Vercel, auto-detect the deployment URL
$vercelUrl = getenv('VERCEL_URL');
$defaultSummarizer = $vercelUrl
    ? "https://{$vercelUrl}/api/summarize"
    : 'http://localhost:8001/api/summarize';

define('SUMMARIZER_URL', getenv('SUMMARIZER_URL') ?: $defaultSummarizer);

// ─── Site Settings ───────────────────────────────────────────────────
define('SITE_NAME', 'Briefly.ai');
define('FETCH_INTERVAL_HOURS', 2);
define('MAX_ARTICLES_PER_CATEGORY', 10);
define('CATEGORIES', ['technology', 'business', 'science']);
define('CATEGORY_LABELS', [
    'technology' => 'Tech',
    'business'   => 'Finance',
    'science'    => 'Science',
]);

// ─── Database Connection (PDO) ───────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = DB_HOST;
        $port = 3306;
        if (strpos($host, ':') !== false) {
            [$host, $port] = explode(':', $host, 2);
        }

        $dsn = "mysql:host={$host};port={$port};dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // TiDB Serverless requires SSL (Secure Transport)
        if (strpos(DB_HOST, 'tidbcloud.com') !== false) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            // Force SSL mode for older PHP/MySQL drivers
            $options[PDO::MYSQL_ATTR_SSL_CA] = true; 
        }

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}
