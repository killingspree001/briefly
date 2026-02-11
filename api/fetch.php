<?php
/**
 * Briefly.ai — The Harvester
 * 
 * Polls the NewsAPI for top headlines by category,
 * deduplicates, and stores raw articles in the database.
 * 
 * Route: /api/fetch (called by cron)
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (empty(NEWS_API_KEY)) {
    echo json_encode(['error' => 'NEWS_API_KEY not set. Add it to your .env or Vercel environment.']);
    http_response_code(500);
    exit;
}

$db = getDB();
$totalInserted = 0;
$errors = [];

foreach (CATEGORIES as $category) {
    $label = CATEGORY_LABELS[$category] ?? ucfirst($category);

    $url = NEWS_API_URL . '?' . http_build_query([
        'category' => $category,
        'language' => 'en',
        'pageSize' => MAX_ARTICLES_PER_CATEGORY,
        'apiKey'   => NEWS_API_KEY,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['User-Agent: Briefly.ai/1.0'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        $errors[] = "Failed to fetch {$label}: HTTP {$httpCode}";
        
        $db->prepare("INSERT INTO fetch_log (category, articles_found, status, error_message) VALUES (?, 0, 'error', ?)")
           ->execute([$label, "HTTP {$httpCode}"]);
        continue;
    }

    $data = json_decode($response, true);
    if (!isset($data['articles']) || !is_array($data['articles'])) {
        $errors[] = "Invalid API response for {$label}";
        continue;
    }

    $insertSQL = "INSERT IGNORE INTO articles 
                  (title, original_url, source_name, description, category, published_at)
                  VALUES (:title, :url, :source, :desc, :cat, :pub)";
    $stmt = $db->prepare($insertSQL);

    $inserted = 0;
    foreach ($data['articles'] as $article) {
        if (empty($article['title']) || $article['title'] === '[Removed]') continue;

        $result = $stmt->execute([
            ':title'  => $article['title'],
            ':url'    => $article['url'] ?? '',
            ':source' => $article['source']['name'] ?? 'Unknown',
            ':desc'   => $article['description'] ?? '',
            ':cat'    => $label,
            ':pub'    => $article['publishedAt'] ? date('Y-m-d H:i:s', strtotime($article['publishedAt'])) : date('Y-m-d H:i:s'),
        ]);

        if ($result && $stmt->rowCount() > 0) $inserted++;
    }

    $totalInserted += $inserted;

    $db->prepare("INSERT INTO fetch_log (category, articles_found, status) VALUES (?, ?, 'success')")
       ->execute([$label, $inserted]);
}

echo json_encode([
    'success'  => true,
    'inserted' => $totalInserted,
    'errors'   => $errors,
]);
