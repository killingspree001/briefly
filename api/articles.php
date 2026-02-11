<?php
/**
 * Briefly.ai — API Endpoint (Articles)
 * 
 * Returns articles as JSON for the frontend AJAX requests.
 * Route: /api/articles?category=Tech&limit=20
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$db = getDB();

$category = isset($_GET['category']) && $_GET['category'] !== 'All'
    ? trim($_GET['category'])
    : null;

$limit = isset($_GET['limit'])
    ? min(50, max(1, (int)$_GET['limit']))
    : 20;

$sql = "SELECT id, title, original_url, source_name, ai_summary, category,
               sentiment, sentiment_score, read_time_sec, published_at
        FROM articles
        WHERE is_processed = 1";

$params = [];

if ($category) {
    $sql .= " AND category = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY published_at DESC LIMIT :limit";

$stmt = $db->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$articles = $stmt->fetchAll();

$formatted = array_map(function ($a) {
    $sec = (int)$a['read_time_sec'];
    $readTime = $sec >= 60 ? round($sec / 60) . ' min' : $sec . ' sec';

    $pubTime = strtotime($a['published_at']);
    $diff = time() - $pubTime;
    if ($diff < 3600) {
        $timeAgo = round($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        $timeAgo = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = round($diff / 86400);
        $timeAgo = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }

    $sentimentIcon = match ($a['sentiment']) {
        'positive' => '↑',
        'negative' => '↓',
        default    => '→',
    };

    return [
        'id'             => (int)$a['id'],
        'title'          => $a['title'],
        'url'            => $a['original_url'],
        'source'         => $a['source_name'],
        'summary'        => $a['ai_summary'],
        'category'       => $a['category'],
        'sentiment'      => ucfirst($a['sentiment']),
        'sentimentIcon'  => $sentimentIcon,
        'sentimentScore' => (float)$a['sentiment_score'],
        'readTime'       => $readTime,
        'timeAgo'        => $timeAgo,
    ];
}, $articles);

$totalStmt = $db->query("SELECT COUNT(*) as total FROM articles WHERE is_processed = 1");
$total = (int)$totalStmt->fetch()['total'];

$lastFetch = $db->query("SELECT MAX(run_at) as last_run FROM fetch_log WHERE status = 'success'");
$lastRun = $lastFetch->fetch()['last_run'];
$lastUpdated = 'Never';
if ($lastRun) {
    $diff = time() - strtotime($lastRun);
    $lastUpdated = $diff < 3600 ? round($diff / 60) . 'm ago' : round($diff / 3600) . 'h ago';
}

echo json_encode([
    'success'      => true,
    'totalStories' => $total,
    'lastUpdated'  => $lastUpdated,
    'articles'     => $formatted,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
