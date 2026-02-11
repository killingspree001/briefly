"""
Briefly.ai — Free AI Summarizer Microservice

A serverless Python function that performs extractive summarization
and sentiment analysis — completely free, no API keys needed.

Works as a Vercel Python serverless function and also locally.
"""

from http.server import BaseHTTPRequestHandler
import json
import re
import math


# ─── Sentiment Word Lists ────────────────────────────────────────────
POSITIVE_WORDS = {
    'achieve', 'advance', 'amazing', 'approval', 'benefit', 'best', 'boost',
    'breakthrough', 'celebrate', 'climb', 'confident', 'cure', 'deliver',
    'discover', 'earn', 'effective', 'efficient', 'enhance', 'excellent',
    'exceed', 'excitement', 'expand', 'gain', 'good', 'great', 'grow',
    'growth', 'happy', 'highest', 'hope', 'improve', 'increase', 'innovate',
    'innovation', 'launch', 'lead', 'milestone', 'opportunity', 'optimistic',
    'outstanding', 'overcome', 'positive', 'profit', 'progress', 'promising',
    'prosper', 'rally', 'record', 'recover', 'recovery', 'reform', 'resolve',
    'revenue', 'reward', 'rise', 'safe', 'save', 'secure', 'soar', 'solution',
    'strong', 'succeed', 'success', 'support', 'surge', 'surpass', 'thrive',
    'top', 'triumph', 'upgrade', 'victory', 'win', 'wonderful', 'efficacy',
}

NEGATIVE_WORDS = {
    'abandon', 'abuse', 'accident', 'attack', 'ban', 'bankrupt', 'blame',
    'block', 'breach', 'break', 'burden', 'cancel', 'catastrophe', 'chaos',
    'collapse', 'concern', 'conflict', 'controversy', 'correction', 'crash',
    'crime', 'crisis', 'critical', 'cut', 'damage', 'danger', 'dead',
    'death', 'debt', 'decline', 'default', 'deficit', 'delay', 'destroy',
    'disaster', 'disease', 'disruption', 'downturn', 'drop', 'emergency',
    'error', 'evict', 'exploit', 'expose', 'fail', 'failure', 'fall', 'fear',
    'fire', 'flood', 'fraud', 'hack', 'harm', 'hurt', 'illegal', 'inflation',
    'injure', 'investigation', 'kill', 'lack', 'lag', 'layoff', 'leak',
    'limit', 'lose', 'loss', 'negative', 'overvalue', 'panic', 'penalty',
    'plunge', 'poor', 'problem', 'protest', 'punish', 'recession', 'reject',
    'resign', 'restrict', 'risk', 'scam', 'scandal', 'shortage', 'shrink',
    'shutdown', 'sink', 'slow', 'slump', 'steal', 'stress', 'struggle',
    'sue', 'suffer', 'suspend', 'tension', 'threat', 'toxic', 'trouble',
    'tumble', 'turmoil', 'unemployment', 'unstable', 'victim', 'violate',
    'volatile', 'warn', 'warning', 'weak', 'worsen', 'worst',
}


def split_sentences(text):
    """Split text into sentences using regex."""
    sentences = re.split(r'(?<=[.!?])\s+', text.strip())
    return [s.strip() for s in sentences if len(s.strip()) > 15]


def score_sentences(sentences):
    """Score sentences by word frequency (TF-based extractive ranking)."""
    # Build word frequency map from all sentences
    word_freq = {}
    for sentence in sentences:
        words = re.findall(r'\b[a-z]+\b', sentence.lower())
        for word in words:
            if len(word) > 3:  # Skip short words
                word_freq[word] = word_freq.get(word, 0) + 1

    if not word_freq:
        return [(s, 0) for s in sentences]

    max_freq = max(word_freq.values())

    # Score each sentence
    scored = []
    for i, sentence in enumerate(sentences):
        words = re.findall(r'\b[a-z]+\b', sentence.lower())
        if not words:
            scored.append((sentence, 0))
            continue

        score = sum(word_freq.get(w, 0) / max_freq for w in words if len(w) > 3)
        score /= max(1, len(words))

        # Boost first sentences (usually more important in news)
        position_boost = 1.0 / (1.0 + i * 0.15)
        score *= position_boost

        scored.append((sentence, score))

    return scored


def summarize(text, max_sentences=3):
    """Generate an extractive summary by picking top-scoring sentences."""
    sentences = split_sentences(text)

    if len(sentences) <= max_sentences:
        return text.strip()

    scored = score_sentences(sentences)
    scored.sort(key=lambda x: x[1], reverse=True)
    top = scored[:max_sentences]

    # Re-order by original position for readability
    top_texts = {s[0] for s in top}
    summary_parts = [s for s in sentences if s in top_texts]

    return ' '.join(summary_parts)


def analyze_sentiment(text):
    """Simple sentiment analysis using word lists."""
    words = set(re.findall(r'\b[a-z]+\b', text.lower()))

    pos_count = len(words & POSITIVE_WORDS)
    neg_count = len(words & NEGATIVE_WORDS)
    total = pos_count + neg_count

    if total == 0:
        return 'neutral', 0.0

    score = (pos_count - neg_count) / total

    if score > 0.15:
        return 'positive', round(min(score, 1.0), 2)
    elif score < -0.15:
        return 'negative', round(max(score, -1.0), 2)
    else:
        return 'neutral', round(score, 2)


class handler(BaseHTTPRequestHandler):
    """Vercel-compatible HTTP handler for the summarizer."""

    def do_POST(self):
        try:
            content_length = int(self.headers.get('Content-Length', 0))
            body = self.rfile.read(content_length)
            data = json.loads(body)

            title = data.get('title', '')
            description = data.get('description', '')

            # Combine title and description for analysis
            full_text = f"{title}. {description}" if description else title

            # Generate summary
            summary = summarize(full_text, max_sentences=3)

            # If summary is too similar to input (short article), clean it up
            if len(summary) > 250:
                summary = summarize(full_text, max_sentences=2)

            # Analyze sentiment
            sentiment, score = analyze_sentiment(full_text)

            result = {
                'summary': summary,
                'sentiment': sentiment,
                'score': score,
            }

            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps(result).encode('utf-8'))

        except Exception as e:
            self.send_response(500)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({
                'error': str(e),
                'summary': title if title else 'Summary unavailable.',
                'sentiment': 'neutral',
                'score': 0.0,
            }).encode('utf-8'))

    def do_OPTIONS(self):
        """Handle CORS preflight."""
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()
