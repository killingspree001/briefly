<?php
/**
 * Briefly.ai — The Presenter (Main Page)
 * 
 * Server-side rendered entry point. PHP generates the initial HTML,
 * then JavaScript handles category filtering via AJAX.
 */

require_once __DIR__ . '/../config.php';

// ─── Fetch initial data for SSR ──────────────────────────────────────
$db = getDB();

// Get today's processed articles only (by fetch date, not source publish date)
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT id, title, original_url, source_name, ai_summary, category,
                           sentiment, sentiment_score, read_time_sec, published_at
                    FROM articles 
                    WHERE is_processed = 1 AND DATE(fetched_at) = :today
                    ORDER BY fetched_at DESC 
                    LIMIT 20");
$stmt->execute([':today' => $today]);
$articles = $stmt->fetchAll();

// Get stats (today only)
$totalStmt = $db->prepare("SELECT COUNT(*) as total FROM articles WHERE is_processed = 1 AND DATE(fetched_at) = :today");
$totalStmt->execute([':today' => $today]);
$totalStories = (int)$totalStmt->fetch()['total'];

$lastFetch = $db->query("SELECT MAX(run_at) as last_run FROM fetch_log WHERE status = 'success'");
$lastRun = $lastFetch->fetch()['last_run'] ?? null;

$lastUpdated = 'Never';
if ($lastRun) {
    $diff = time() - strtotime($lastRun);
    if ($diff < 3600) {
        $lastUpdated = round($diff / 60) . 'm ago';
    } else {
        $lastUpdated = round($diff / 3600) . 'h ago';
    }
}

/**
 * Helper: format read time
 */
function formatReadTime(int $sec): string {
    return $sec >= 60 ? round($sec / 60) . ' min' : $sec . ' sec';
}

/**
 * Helper: format time ago
 */
function formatTimeAgo(?string $datetime): string {
    if (!$datetime) return 'Unknown';
    $diff = time() - strtotime($datetime);
    if ($diff < 3600) return round($diff / 60) . ' minutes ago';
    if ($diff < 86400) {
        $h = round($diff / 3600);
        return $h . ' hour' . ($h > 1 ? 's' : '') . ' ago';
    }
    $d = round($diff / 86400);
    return $d . ' day' . ($d > 1 ? 's' : '') . ' ago';
}

/**
 * Helper: sentiment arrow
 */
function sentimentIcon(string $sentiment): string {
    return match ($sentiment) {
        'positive' => '↑',
        'negative' => '↓',
        default    => '→',
    };
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Briefly.ai — AI-powered global news intelligence. Get 60-second summaries of the world's most important stories.">
    <title>Briefly.ai — The Automated Global Intelligence Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>

    <!-- ─── Header ─────────────────────────────────────────────────── -->
    <header class="header" id="header">
        <div class="header__inner">
            <a href="/" class="header__logo">
                <div class="logo-icon" id="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                </div>
                <span class="header__title"><?= SITE_NAME ?></span>
            </a>
            <div class="header__status">
                <span class="status-dot" id="status-dot"></span>
                <span class="status-text">AI Engine Active</span>
            </div>
        </div>
    </header>

    <!-- ─── Main Content ───────────────────────────────────────────── -->
    <main class="main">
        <!-- Filter Bar -->
        <div class="filter-bar" id="filter-bar">
            <div class="filter-bar__pills">
                <button class="pill pill--active" data-category="All" id="pill-all">
                    <svg class="pill__icon" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
                    All
                </button>
                <button class="pill" data-category="Tech" id="pill-tech">
                    <svg class="pill__icon" viewBox="0 0 16 16" fill="currentColor"><rect x="2" y="3" width="12" height="8" rx="1"/><line x1="5" y1="13" x2="11" y2="13" stroke="currentColor" stroke-width="1.5"/></svg>
                    Tech
                </button>
                <button class="pill" data-category="Finance" id="pill-finance">
                    <svg class="pill__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="2 12 6 6 10 9 14 3"/></svg>
                    Finance
                </button>
                <button class="pill" data-category="Science" id="pill-science">
                    <svg class="pill__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2v5L3 13a1 1 0 001 1h8a1 1 0 001-1l-3-6V2M5 2h6"/></svg>
                    Science
                </button>
            </div>
            <div class="filter-bar__meta" id="meta-info">
                <span class="today-label">📅 <?= date('M j, Y') ?></span>
                <span class="meta-separator">·</span>
                <span id="story-count"><?= $totalStories ?> stories</span>
                <span class="meta-separator">·</span>
                <span>Updated <span id="last-updated"><?= htmlspecialchars($lastUpdated) ?></span></span>
                <span class="meta-separator">·</span>
                <a href="/api/pdf" class="pdf-btn" id="pdf-download" title="Download today's briefing as PDF">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" width="14" height="14"><path d="M2 10v3a1 1 0 001 1h10a1 1 0 001-1v-3"/><polyline points="5 7 8 10 11 7"/><line x1="8" y1="2" x2="8" y2="10"/></svg>
                    PDF
                </a>
                <span class="meta-separator">·</span>
                <a href="/archive" class="archive-btn" id="archive-link" title="View past 7 days">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" width="14" height="14"><rect x="2" y="2" width="12" height="12" rx="2"/><line x1="2" y1="6" x2="14" y2="6"/><line x1="6" y1="2" x2="6" y2="6"/></svg>
                    Archive
                </a>
            </div>
        </div>

        <!-- News Grid -->
        <div class="news-grid" id="news-grid">
            <?php if (empty($articles)): ?>
                <div class="empty-state" id="empty-state">
                    <div class="empty-state__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <h2>No stories yet</h2>
                    <p>Run <code>php seed_demo.php</code> to load demo data, or <code>php api/cron.php</code> to fetch live news.</p>
                    <p class="empty-state__archive-hint">
                        Check the <a href="/archive">Archive <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-left:2px;"><line x1="1" y1="8" x2="15" y2="8"/><polyline points="8 1 15 8 8 15"/></svg></a> for yesterday's news.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article class="card" id="card-<?= $article['id'] ?>">
                        <div class="card__header">
                            <span class="card__category card__category--<?= strtolower($article['category']) ?>">
                                <?= htmlspecialchars($article['category']) ?>
                            </span>
                            <span class="card__read-time">
                                <svg class="card__clock-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><polyline points="8 4 8 8 11 10"/></svg>
                                <?= formatReadTime((int)$article['read_time_sec']) ?>
                            </span>
                        </div>

                        <h2 class="card__title">
                            <?= htmlspecialchars($article['title']) ?>
                        </h2>

                        <p class="card__summary">
                            <?= htmlspecialchars($article['ai_summary'] ?? $article['description'] ?? '') ?>
                        </p>

                        <div class="card__footer">
                            <div class="card__meta">
                                <span class="card__sentiment card__sentiment--<?= $article['sentiment'] ?>">
                                    <?= sentimentIcon($article['sentiment']) ?> <?= ucfirst($article['sentiment']) ?>
                                </span>
                                <span class="card__source"><?= htmlspecialchars($article['source_name'] ?? 'Unknown') ?></span>
                                <span class="card__time-ago"><?= formatTimeAgo($article['published_at']) ?></span>
                            </div>
                            <div class="card__actions">
                                <button class="card__action-btn card__action-btn--labeled" onclick="shareArticle(<?= $article['id'] ?>, '<?= addslashes($article['title']) ?>', '<?= addslashes($article['original_url']) ?>')" title="Share this summary" aria-label="Share article">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="4" cy="8" r="2"/><circle cx="12" cy="4" r="2"/><circle cx="12" cy="12" r="2"/><line x1="6" y1="7" x2="10" y2="5"/><line x1="6" y1="9" x2="10" y2="11"/></svg>
                                    <span>Share</span>
                                </button>
                                <a class="card__action-btn card__action-btn--labeled" href="<?= htmlspecialchars($article['original_url']) ?>" target="_blank" rel="noopener noreferrer" title="Read the full story on <?= htmlspecialchars($article['source_name'] ?? 'source') ?>" aria-label="Open original article">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v4a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1h4"/><polyline points="8 2 14 2 14 8"/><line x1="14" y1="2" x2="6" y2="10"/></svg>
                                    <span>Read</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- ─── Footer ─────────────────────────────────────────────────── -->
    <footer class="footer" id="footer">
        <div class="footer__inner">
            <span>⚡ <?= SITE_NAME ?> — Automated Global Intelligence Hub</span>
            <span class="footer__separator">·</span>
            <span>Powered by AI</span>
        </div>
    </footer>

    <!-- ─── Theme Toggle (floating) ────────────────────────────────── -->
    <button class="theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle dark/light mode">
        <svg class="theme-toggle__icon theme-toggle__icon--dark" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
        </svg>
        <svg class="theme-toggle__icon theme-toggle__icon--light" viewBox="0 0 20 20" fill="currentColor">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
        </svg>
    </button>

    <script src="/public/app.js"></script>
</body>
</html>
