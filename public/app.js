/**
 * Briefly.ai — Frontend Logic
 * 
 * Handles category filtering (AJAX), theme toggle, share functionality,
 * and skeleton loading states.
 */

// ─── State ───────────────────────────────────────────────────────────
let activeCategory = 'All';
let isLoading = false;

// ─── DOM Elements ────────────────────────────────────────────────────
const grid = document.getElementById('news-grid');
const pills = document.querySelectorAll('.pill');
const storyCount = document.getElementById('story-count');
const lastUpdated = document.getElementById('last-updated');
const themeToggle = document.getElementById('theme-toggle');

// ─── Category Filter ─────────────────────────────────────────────────
pills.forEach(pill => {
    pill.addEventListener('click', () => {
        const category = pill.dataset.category;
        if (category === activeCategory || isLoading) return;

        // Update active pill
        pills.forEach(p => p.classList.remove('pill--active'));
        pill.classList.add('pill--active');
        activeCategory = category;

        // Fetch filtered articles
        fetchArticles(category);
    });
});

/**
 * Fetch articles from the API endpoint and re-render the grid.
 */
async function fetchArticles(category) {
    isLoading = true;
    showSkeletons();

    try {
        const params = new URLSearchParams();
        if (category && category !== 'All') {
            params.set('category', category);
        }
        params.set('limit', '20');

        const response = await fetch(`/api/articles?${params.toString()}`);
        const data = await response.json();

        if (data.success) {
            renderArticles(data.articles);
            storyCount.textContent = data.totalStories + ' stories';
            lastUpdated.textContent = data.lastUpdated;
        } else {
            renderError();
        }
    } catch (err) {
        console.error('Fetch error:', err);
        renderError();
    } finally {
        isLoading = false;
    }
}

/**
 * Show skeleton loading cards while fetching.
 */
function showSkeletons() {
    let html = '';
    for (let i = 0; i < 6; i++) {
        html += `
            <div class="skeleton-card">
                <div style="display:flex;justify-content:space-between;">
                    <div class="skeleton-line skeleton-line--short"></div>
                    <div class="skeleton-line skeleton-line--short"></div>
                </div>
                <div class="skeleton-line skeleton-line--title"></div>
                <div class="skeleton-line skeleton-line--long"></div>
                <div class="skeleton-line skeleton-line--medium"></div>
                <div class="skeleton-line skeleton-line--long"></div>
                <div style="display:flex;gap:10px;margin-top:auto;padding-top:14px;border-top:1px solid var(--border);">
                    <div class="skeleton-line skeleton-line--short"></div>
                    <div class="skeleton-line skeleton-line--short"></div>
                </div>
            </div>
        `;
    }
    grid.innerHTML = html;
}

/**
 * Render articles into the grid with staggered entrance animations.
 */
function renderArticles(articles) {
    if (articles.length === 0) {
        grid.innerHTML = `
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                </div>
                <h2>No stories found</h2>
                <p>No articles match this category yet. Try checking back later.</p>
            </div>
        `;
        return;
    }

    let html = '';
    articles.forEach((a, index) => {
        const sentimentClass = a.sentiment.toLowerCase();
        const escapedTitle = escapeHtml(a.title);
        const escapedUrl = escapeHtml(a.url);

        html += `
            <article class="card card--entering" style="animation-delay: ${index * 60}ms" id="card-${a.id}">
                <div class="card__header">
                    <span class="card__category card__category--${a.category.toLowerCase()}">
                        ${escapeHtml(a.category)}
                    </span>
                    <span class="card__read-time">
                        <svg class="card__clock-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><polyline points="8 4 8 8 11 10"/></svg>
                        ${escapeHtml(a.readTime)}
                    </span>
                </div>
                <h2 class="card__title">${escapedTitle}</h2>
                <p class="card__summary">${escapeHtml(a.summary || '')}</p>
                <div class="card__footer">
                    <div class="card__meta">
                        <span class="card__sentiment card__sentiment--${sentimentClass}">
                            ${a.sentimentIcon} ${a.sentiment}
                        </span>
                        <span class="card__source">${escapeHtml(a.source)}</span>
                        <span class="card__time-ago">${escapeHtml(a.timeAgo)}</span>
                    </div>
                    <div class="card__actions">
                        <button class="card__action-btn" onclick="shareArticle(${a.id}, '${escapedTitle.replace(/'/g, "\\'")}', '${escapedUrl.replace(/'/g, "\\'")}')" title="Share" aria-label="Share article">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="4" cy="8" r="2"/><circle cx="12" cy="4" r="2"/><circle cx="12" cy="12" r="2"/><line x1="6" y1="7" x2="10" y2="5"/><line x1="6" y1="9" x2="10" y2="11"/></svg>
                        </button>
                        <a class="card__action-btn" href="${escapedUrl}" target="_blank" rel="noopener noreferrer" title="Read full story" aria-label="Open original article">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v4a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1h4"/><polyline points="8 2 14 2 14 8"/><line x1="14" y1="2" x2="6" y2="10"/></svg>
                        </a>
                    </div>
                </div>
            </article>
        `;
    });

    grid.innerHTML = html;
}

/**
 * Show an error state in the grid.
 */
function renderError() {
    grid.innerHTML = `
        <div class="empty-state">
            <div class="empty-state__icon" style="border-color: var(--negative);">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--negative)" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h2>Something went wrong</h2>
            <p>Could not fetch articles. Please check your connection and try again.</p>
        </div>
    `;
}

// ─── Share Functionality ─────────────────────────────────────────────
function shareArticle(id, title, url) {
    const text = `📰 ${title}\n\nRead more: ${url}\n\n— via Briefly.ai`;

    if (navigator.share) {
        navigator.share({ title, text, url }).catch(() => { });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!');
        }).catch(() => {
            // Final fallback
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showToast('Copied to clipboard!');
        });
    }
}

// ─── Toast Notifications ─────────────────────────────────────────────
function showToast(message) {
    // Remove existing toast
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => {
        toast.classList.add('toast--visible');
    });

    // Auto-hide
    setTimeout(() => {
        toast.classList.remove('toast--visible');
        setTimeout(() => toast.remove(), 400);
    }, 2500);
}

// ─── Theme Toggle ────────────────────────────────────────────────────
const THEME_KEY = 'briefly_theme';

function initTheme() {
    const saved = localStorage.getItem(THEME_KEY);
    if (saved) {
        document.documentElement.setAttribute('data-theme', saved);
    }
}

themeToggle.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem(THEME_KEY, next);
});

initTheme();

// ─── Utility ─────────────────────────────────────────────────────────
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
