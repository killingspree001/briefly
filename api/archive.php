<?php
/**
 * Briefly.ai — Archive (Past 7 Days)
 * 
 * Shows articles from the previous 7 days, grouped by date.
 * Route: /archive
 */

require_once __DIR__ . '/../config.php';

$db = getDB();

// Get articles from the past 7 days (excluding today) based on when we fetched them
$stmt = $db->prepare("SELECT id, title, original_url, source_name, ai_summary, category,
                             sentiment, sentiment_score, read_time_sec, published_at,
                             DATE(fetched_at) as pub_date
                      FROM articles 
                      WHERE is_processed = 1 AND DATE(fetched_at) < :today
                        AND DATE(fetched_at) >= DATE_SUB(:today2, INTERVAL 7 DAY)
                      ORDER BY fetched_at DESC");
$today = date('Y-m-d');
$stmt->execute([':today' => $today, ':today2' => $today]);
$allArticles = $stmt->fetchAll();

// Group by date
$grouped = [];
foreach ($allArticles as $article) {
    $date = $article['pub_date'];
    if (!isset($grouped[$date])) $grouped[$date] = [];
    $grouped[$date][] = $article;
}

// Helpers
function formatReadTime(int $sec): string {
    return $sec >= 60 ? round($sec / 60) . ' min' : $sec . ' sec';
}

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

function sentimentIcon(string $sentiment): string {
    return match ($sentiment) {
        'positive' => '↑', 'negative' => '↓', default => '→',
    };
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Briefly.ai — Past 7 days of AI-summarized global news.">
    <title>Archive — Briefly.ai</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
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
                <span class="status-text">Archive · Past 7 Days</span>
            </div>
        </div>
    </header>

    <!-- ─── Main Content ───────────────────────────────────────────── -->
    <main class="main">
        <!-- Navigation -->
        <div class="archive-nav">
            <a href="/" class="back-btn" id="back-to-today">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><polyline points="10 2 4 8 10 14"/></svg>
                Back to Today
            </a>
            <span class="archive-nav__count"><?= count($allArticles) ?> articles across <?= count($grouped) ?> days</span>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="empty-state" id="empty-state">
                <div class="empty-state__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <h2>No archived stories</h2>
                <p>Articles from the past 7 days will appear here automatically.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $date => $articles): ?>
                <?php
                    $dateObj = new DateTime($date);
                    $dayLabel = $dateObj->format('l, F j, Y');
                    $dayCount = count($articles);
                ?>
                <div class="archive-day">
                    <div class="archive-day__header">
                        <h2 class="archive-day__title">📰 <?= $dayLabel ?></h2>
                        <span class="archive-day__count"><?= $dayCount ?> <?= $dayCount === 1 ? 'story' : 'stories' ?></span>
                    </div>
                    <div class="news-grid">
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

                                <h2 class="card__title"><?= htmlspecialchars($article['title']) ?></h2>
                                <p class="card__summary"><?= htmlspecialchars($article['ai_summary'] ?? '') ?></p>

                                <div class="card__footer">
                                    <div class="card__meta">
                                        <span class="card__sentiment card__sentiment--<?= $article['sentiment'] ?>">
                                            <?= sentimentIcon($article['sentiment']) ?> <?= ucfirst($article['sentiment']) ?>
                                        </span>
                                        <span class="card__source"><?= htmlspecialchars($article['source_name'] ?? 'Unknown') ?></span>
                                        <span class="card__time-ago"><?= formatTimeAgo($article['published_at']) ?></span>
                                    </div>
                                    <div class="card__actions">
                                        <a class="card__action-btn card__action-btn--labeled" href="<?= htmlspecialchars($article['original_url']) ?>" target="_blank" rel="noopener noreferrer" title="Read on <?= htmlspecialchars($article['source_name'] ?? 'source') ?>">
                                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v4a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1h4"/><polyline points="8 2 14 2 14 8"/><line x1="14" y1="2" x2="6" y2="10"/></svg>
                                            <span>Read</span>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- ─── Footer ─────────────────────────────────────────────────── -->
    <footer class="footer" id="footer">
        <div class="footer__inner">
            <span>⚡ <?= SITE_NAME ?> — Automated Global Intelligence Hub</span>
            <span class="footer__separator">·</span>
            <span>Powered by AI</span>
        </div>
    </footer>

    <!-- ─── Theme Toggle ────────────────────────────────────────────── -->
    <button class="theme-toggle" id="theme-toggle" title="Toggle theme" aria-label="Toggle dark/light mode">
        <svg class="theme-toggle__icon theme-toggle__icon--dark" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
        </svg>
        <svg class="theme-toggle__icon theme-toggle__icon--light" viewBox="0 0 20 20" fill="currentColor">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
        </svg>
    </button>

    <script>
        // Theme toggle
        const THEME_KEY = 'briefly_theme';
        const themeToggle = document.getElementById('theme-toggle');

        function initTheme() {
            const saved = localStorage.getItem(THEME_KEY);
            if (saved) document.documentElement.setAttribute('data-theme', saved);
        }

        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem(THEME_KEY, next);
        });

        initTheme();
    </script>
</body>
</html>
