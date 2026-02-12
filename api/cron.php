<?php
/**
 * Briefly.ai — Cron Orchestrator
 * 
 * Runs the Harvester then the Distiller in sequence.
 * 
 * Local:  php api/cron.php
 * Vercel: Set up a Vercel Cron Job hitting /api/cron
 * 
 * To set up on Vercel, add to vercel.json:
 *   "crons": [{ "path": "/api/cron", "schedule": "0 */4 * * *" }]
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$results = [];

// Detect base URL
if (getenv('VERCEL_URL')) {
    $baseUrl = 'https://' . getenv('VERCEL_URL');
} elseif (isset($_SERVER['HTTP_HOST'])) {
    $proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $baseUrl = "{$proto}://{$_SERVER['HTTP_HOST']}";
} else {
    $baseUrl = 'http://localhost:8000';
}

// Step 1: Fetch news
$ch = curl_init("{$baseUrl}/api/fetch");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
$fetchResult = curl_exec($ch);
curl_close($ch);
$results['fetch'] = json_decode($fetchResult, true) ?: ['error' => 'Fetch failed'];

// Step 2: Process with AI
$ch = curl_init("{$baseUrl}/api/process");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
$processResult = curl_exec($ch);
curl_close($ch);
$results['process'] = json_decode($processResult, true) ?: ['error' => 'Process failed'];

echo json_encode([
    'success'   => true,
    'timestamp' => date('c'),
    'results'   => $results,
], JSON_PRETTY_PRINT);
