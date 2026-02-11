<?php
/**
 * Briefly.ai — Demo Seeder
 * 
 * Populates the database with realistic sample articles so you can
 * preview the UI immediately — no API keys required.
 * 
 * Usage: php seed_demo.php
 */

require_once __DIR__ . '/config.php';

$db = getDB();

echo "[Seeder] Populating demo data...\n";

$demoArticles = [
    // ─── Tech ────────────────────────────────────────────────────────
    [
        'title'       => 'OpenAI Unveils GPT-5 with Real-Time Reasoning Capabilities',
        'url'         => 'https://techcrunch.com/example/gpt-5-launch',
        'source'      => 'TechCrunch',
        'description' => 'OpenAI has launched GPT-5, featuring advanced real-time reasoning.',
        'summary'     => 'OpenAI has launched GPT-5, featuring advanced real-time reasoning and multimodal understanding. The model processes text, images, and audio simultaneously, marking a significant leap in AI capability for enterprise applications.',
        'category'    => 'Tech',
        'sentiment'   => 'positive',
        'score'       => 0.85,
        'read_time'   => 45,
        'published'   => date('Y-m-d H:i:s', strtotime('-2 hours')),
    ],
    [
        'title'       => "Apple's Mixed Reality Headset Outsells Expectations by 300%",
        'url'         => 'https://theverge.com/example/apple-mr-headset',
        'source'      => 'The Verge',
        'description' => 'Apple Vision Pro successor sees massive sales.',
        'summary'     => "Apple's second-generation Vision Pro has exceeded Wall Street's projections by over 300%, driven by enterprise demand and a new \$1,499 consumer model. The headset features improved passthrough and gesture controls.",
        'category'    => 'Tech',
        'sentiment'   => 'positive',
        'score'       => 0.90,
        'read_time'   => 30,
        'published'   => date('Y-m-d H:i:s', strtotime('-5 hours')),
    ],
    [
        'title'       => 'Google DeepMind Achieves Breakthrough in Protein Folding Speed',
        'url'         => 'https://wired.com/example/deepmind-proteins',
        'source'      => 'Wired',
        'description' => 'AlphaFold 4 reduces computation time.',
        'summary'     => "Google DeepMind's AlphaFold 4 can now predict protein structures in under 10 seconds, a 100x speed improvement. This breakthrough could accelerate drug discovery timelines from years to months.",
        'category'    => 'Tech',
        'sentiment'   => 'positive',
        'score'       => 0.95,
        'read_time'   => 50,
        'published'   => date('Y-m-d H:i:s', strtotime('-8 hours')),
    ],

    // ─── Finance ─────────────────────────────────────────────────────
    [
        'title'       => 'Federal Reserve Signals Potential Rate Cut in Q2 2026',
        'url'         => 'https://bloomberg.com/example/fed-rate-cut',
        'source'      => 'Bloomberg',
        'description' => 'The Federal Reserve hinted at a potential interest rate reduction.',
        'summary'     => 'The Federal Reserve hinted at a potential interest rate reduction in Q2, citing easing inflation and steady employment growth. Markets responded positively with S&P 500 futures climbing 1.2% in after-hours trading.',
        'category'    => 'Finance',
        'sentiment'   => 'positive',
        'score'       => 0.70,
        'read_time'   => 60,
        'published'   => date('Y-m-d H:i:s', strtotime('-3 hours')),
    ],
    [
        'title'       => 'Bitcoin ETFs See Record $4.2B Weekly Inflow',
        'url'         => 'https://coindesk.com/example/btc-etf-inflow',
        'source'      => 'CoinDesk',
        'description' => 'Institutional Bitcoin investment hits all-time high.',
        'summary'     => 'Spot Bitcoin ETFs recorded a historic \$4.2 billion in weekly inflows, led by BlackRock\'s IBIT fund. Analysts attribute the surge to increasing institutional adoption and growing regulatory clarity worldwide.',
        'category'    => 'Finance',
        'sentiment'   => 'positive',
        'score'       => 0.80,
        'read_time'   => 45,
        'published'   => date('Y-m-d H:i:s', strtotime('-6 hours')),
    ],
    [
        'title'       => 'European Central Bank Warns of Housing Market Correction',
        'url'         => 'https://ft.com/example/ecb-housing',
        'source'      => 'Financial Times',
        'description' => 'ECB raises concerns about real estate valuations.',
        'summary'     => 'The ECB published a stability report warning that commercial real estate in major European cities remains overvalued by 15-20%. The report recommends banks increase capital buffers as a precautionary measure.',
        'category'    => 'Finance',
        'sentiment'   => 'negative',
        'score'       => -0.60,
        'read_time'   => 55,
        'published'   => date('Y-m-d H:i:s', strtotime('-10 hours')),
    ],

    // ─── Science ─────────────────────────────────────────────────────
    [
        'title'       => 'CRISPR Gene Therapy Achieves 94% Success Rate in Clinical Trial',
        'url'         => 'https://nature.com/example/crispr-trial',
        'source'      => 'Nature',
        'description' => 'Landmark CRISPR gene therapy clinical trial results.',
        'summary'     => 'A landmark clinical trial reports 94% efficacy for a CRISPR-based gene therapy targeting sickle cell disease. The treatment permanently corrects the genetic mutation, potentially eliminating the need for lifelong medication.',
        'category'    => 'Science',
        'sentiment'   => 'positive',
        'score'       => 0.95,
        'read_time'   => 60,
        'published'   => date('Y-m-d H:i:s', strtotime('-4 hours')),
    ],
    [
        'title'       => "NASA's Europa Clipper Detects Signs of Subsurface Ocean Activity",
        'url'         => 'https://nasa.gov/example/europa-clipper',
        'source'      => 'NASA',
        'description' => 'Europa mission finds evidence of ocean beneath the ice.',
        'summary'     => "NASA's Europa Clipper spacecraft has detected thermal plumes and chemical signatures consistent with hydrothermal vents beneath Europa's ice shell. Scientists say this significantly increases the probability of microbial life.",
        'category'    => 'Science',
        'sentiment'   => 'positive',
        'score'       => 0.90,
        'read_time'   => 60,
        'published'   => date('Y-m-d H:i:s', strtotime('-7 hours')),
    ],
    [
        'title'       => 'New Study Links Microplastics to Accelerated Cellular Aging',
        'url'         => 'https://sciencedaily.com/example/microplastics',
        'source'      => 'Science Daily',
        'description' => 'Researchers find troubling connection between microplastics and aging.',
        'summary'     => 'A study published in The Lancet reveals that microplastic particles accumulate in human organ tissue and may accelerate cellular aging. Researchers found a 23% increase in oxidative stress markers in heavily exposed populations.',
        'category'    => 'Science',
        'sentiment'   => 'negative',
        'score'       => -0.75,
        'read_time'   => 50,
        'published'   => date('Y-m-d H:i:s', strtotime('-12 hours')),
    ],
];

$sql = "INSERT IGNORE INTO articles 
        (title, original_url, source_name, description, ai_summary, category, sentiment, sentiment_score, read_time_sec, published_at, is_processed)
        VALUES (:title, :url, :source, :desc, :summary, :cat, :sentiment, :score, :read_time, :pub, 1)";

$stmt = $db->prepare($sql);
$inserted = 0;

foreach ($demoArticles as $article) {
    $result = $stmt->execute([
        ':title'     => $article['title'],
        ':url'       => $article['url'],
        ':source'    => $article['source'],
        ':desc'      => $article['description'],
        ':summary'   => $article['summary'],
        ':cat'       => $article['category'],
        ':sentiment' => $article['sentiment'],
        ':score'     => $article['score'],
        ':read_time' => $article['read_time'],
        ':pub'       => $article['published'],
    ]);
    if ($result) $inserted++;
}

// Also insert a fake fetch log entry so the "Last updated" shows correctly
$db->exec("INSERT INTO fetch_log (category, articles_found, status) VALUES ('all', {$inserted}, 'success')");

echo "[Seeder] Inserted {$inserted} demo articles.\n";
echo "[Seeder] Done! Open index.php in your browser to see the result.\n";
