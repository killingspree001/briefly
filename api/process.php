<?php
/**
 * Briefly.ai — The Distiller (Free AI via Python Microservice)
 * 
 * Sends unprocessed articles to the Python summarizer endpoint,
 * which does extractive summarization and sentiment analysis — for free.
 * 
 * Route: /api/process (called by cron)
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$db = getDB();

// Get unprocessed articles
$stmt = $db->query("SELECT id, title, description FROM articles WHERE is_processed = 0 ORDER BY id ASC LIMIT 20");
$articles = $stmt->fetchAll();

if (empty($articles)) {
    echo json_encode(['success' => true, 'processed' => 0, 'message' => 'No articles to process.']);
    exit;
}

$processed = 0;
$errors = [];

$updateSQL = "UPDATE articles 
              SET ai_summary = :summary, 
                  sentiment = :sentiment, 
                  sentiment_score = :score, 
                  read_time_sec = :read_time,
                  is_processed = 1 
              WHERE id = :id";
$updateStmt = $db->prepare($updateSQL);

foreach ($articles as $article) {
    // ─── Call the Python summarizer ──────────────────────────────────
    $payload = json_encode([
        'title'       => $article['title'],
        'description' => $article['description'] ?? '',
    ]);

    $ch = curl_init(SUMMARIZER_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        // Fallback: use the description as-is
        $summary   = $article['description'] ?: $article['title'];
        $sentiment = 'neutral';
        $score     = 0.0;
    } else {
        $data = json_decode($response, true);
        $summary   = $data['summary']   ?? $article['description'] ?? $article['title'];
        $sentiment = $data['sentiment'] ?? 'neutral';
        $score     = (float)($data['score'] ?? 0.0);
    }

    // Calculate read time from summary
    $wordCount = str_word_count($summary);
    $readTimeSec = max(15, round(($wordCount / 200) * 60)); // ~200 WPM reading speed

    // Update the article
    $updateStmt->execute([
        ':summary'   => $summary,
        ':sentiment' => $sentiment,
        ':score'     => $score,
        ':read_time' => $readTimeSec,
        ':id'        => $article['id'],
    ]);

    $processed++;
}

echo json_encode([
    'success'   => true,
    'processed' => $processed,
    'errors'    => $errors,
]);
