<?php
/**
 * Briefly.ai — Client Demo Seeder
 * 
 * Populates the database with the specific news items provided by the user
 * for the period Feb 5 - Feb 12, 2026.
 */

require_once __DIR__ . '/config.php';

$db = getDB();

$stories = [
    // --- Feb 12 ---
    [
        'title' => "ECB Official Proposes New '28th Regime' to Scale European Tech",
        'desc' => "Isabel Schnabel of the European Central Bank argued for a unified legal framework that would allow European startups to scale across all EU nations without 27 different sets of rules.",
        'url' => "https://www.ecb.europa.io/press/blog/date/2026/html/ecb.blog20260212~89f473cdba.en.html",
        'cat' => "Finance",
        'date' => "2026-02-12 09:00:00",
        'sentiment' => 'positive',
        'score' => 0.6
    ],
    // --- Feb 11 ---
    [
        'title' => "US Plans 'Big Tech Carve-out' from New Chip Tariffs",
        'desc' => "The US is considering specific exemptions for major tech firms from upcoming semiconductor tariffs. This is a massive win for companies like OpenAI and Microsoft.",
        'url' => "https://www.thehindu.com/sci-tech/technology/",
        'cat' => "Tech",
        'date' => "2026-02-11 14:00:00",
        'sentiment' => 'positive',
        'score' => 0.7
    ],
    [
        'title' => "Amazon and Alphabet Spark 'Bubble' Fears with Massive AI Spending Plans",
        'desc' => "Amazon plans to spend $200 billion this year on AI infrastructure, while Alphabet is aiming for $185 billion, causing market jitters about ROI.",
        'url' => "https://jerseyeveningpost.com/business/2026/02/11/techs-big-investment-in-artificial-intelligence-fuels-bubble-concerns/",
        'cat' => "Tech",
        'date' => "2026-02-11 10:00:00",
        'sentiment' => 'neutral',
        'score' => 0.1
    ],
    [
        'title' => "Nvidia Finalizes Taipei HQ; Shifts Strategy for 2028",
        'desc' => "Nvidia is breaking ground on its first overseas HQ in Taipei this June, and rumors suggest a production shift toward Intel.",
        'url' => "https://www.digitimes.com/tech/",
        'cat' => "Tech",
        'date' => "2026-02-11 08:00:00",
        'sentiment' => 'neutral',
        'score' => 0.2
    ],
    [
        'title' => "Alphabet to Issue 100-Year Bond to Fund AI Research",
        'desc' => "In a rare financial move, Google's parent announced a century-long bond sale, betting on AI dominance for the next 100 years.",
        'url' => "https://jerseyeveningpost.com/business/2026/02/11/techs-big-investment-in-artificial-intelligence-fuels-bubble-concerns/",
        'cat' => "Tech",
        'date' => "2026-02-11 16:00:00",
        'sentiment' => 'positive',
        'score' => 0.5
    ],
    [
        'title' => "Scientists Find Electrons 'Smiling' in Earth’s Magnetic Shield",
        'desc' => "Researchers found a smile-shaped velocity pattern in electrons within Earth's magnetosphere, helping predict space weather impacts.",
        'url' => "https://www.unh.edu/news/2026/02/scientists-find-electrons-smiling-earths-magnetic-shield",
        'cat' => "Science",
        'date' => "2026-02-11 11:00:00",
        'sentiment' => 'positive',
        'score' => 0.4
    ],
    [
        'title' => "New Model Proves Skin is the 'Initiator' of Lupus",
        'desc' => "A breakthrough study revealed that defects in skin cells can drive the systemic progression of Lupus, shifting treatment focus.",
        'url' => "https://www.eurekalert.org/news-releases/1116163",
        'cat' => "Science",
        'date' => "2026-02-11 09:00:00",
        'sentiment' => 'positive',
        'score' => 0.8
    ],
    // --- Feb 10 ---
    [
        'title' => "AI Interprets Brain Scans in Seconds to Flag Emergencies",
        'desc' => "A new AI system can read MRI scans in seconds to identify strokes or hemorrhages instantly, potentially saving thousands of lives.",
        'url' => "https://www.sciencedaily.com/releases/2026/02/260210141221.htm",
        'cat' => "Science",
        'date' => "2026-02-10 12:00:00",
        'sentiment' => 'positive',
        'score' => 0.95
    ],
    [
        'title' => "AI and Human Math Team Up to Solve Fluid Turbulence",
        'desc' => "By teaching AI the laws of physics instead of just data, scientists created a model that accurately simulates air and water swirling.",
        'url' => "https://news.uchicago.edu/story/scientists-pair-ai-and-human-knowledge-tackle-notoriously-difficult-physics-question",
        'cat' => "Science",
        'date' => "2026-02-10 15:00:00",
        'sentiment' => 'positive',
        'score' => 0.75
    ],
    // --- Feb 9 ---
    [
        'title' => "IMF and Saudi Finance Ministry Conclude AlUla Conference",
        'desc' => "Global leaders discussed how emerging markets can survive uncertainty, emphasizing strong policy frameworks against tech shifts.",
        'url' => "https://www.imf.org/en/news/articles/2026/02/09/pr-26038-joint-statement-on-conclusion-of-2nd-annual-alula-conf-for-emerging-market-economies",
        'cat' => "Finance",
        'date' => "2026-02-09 13:00:00",
        'sentiment' => 'neutral',
        'score' => 0.1
    ],
    [
        'title' => "Mediterranean Diet Slashes Stroke Risk in Women",
        'desc' => "A massive study confirmed that plant-based foods, fish, and olive oil significantly drop stroke risk in women.",
        'url' => "https://www.sciencedaily.com/news/top/science/",
        'cat' => "Science",
        'date' => "2026-02-09 10:00:00",
        'sentiment' => 'positive',
        'score' => 0.85
    ],
    [
        'title' => "Nigeria’s DMO Honored for Fixed-Income Excellence",
        'desc' => "Nigeria's Debt Management Office received recognition for Green Bonds and Sukuk, signaling international confidence.",
        'url' => "https://www.dmo.gov.ng/news-and-events/dmo-in-the-news",
        'cat' => "Finance",
        'date' => "2026-02-09 11:00:00",
        'sentiment' => 'positive',
        'score' => 0.6
    ],
    // --- Feb 8 ---
    [
        'title' => "Gold and Crypto Markets Struggle After 'Black Friday' Crash",
        'desc' => "Following a historic collapse in gold and silver, the past week has been all about a 'repair phase' as traders wait for stability.",
        'url' => "https://fxtrendo.com/weekly-outlook-february-8-14-2026/",
        'cat' => "Finance",
        'date' => "2026-02-08 17:00:00",
        'sentiment' => 'negative',
        'score' => -0.5
    ],
    [
        'title' => "Subaru-Asahi StarCam Reveals Secret Life of Meteor Clusters",
        'desc' => "New 'meteor clusters' previously invisible have been discovered in Hawaii, helping us understand solar system formation.",
        'url' => "https://subarutelescope.org/en/news/topics/2026/02/08/3661.html",
        'cat' => "Science",
        'date' => "2026-02-08 22:00:00",
        'sentiment' => 'positive',
        'score' => 0.45
    ],
    // --- Feb 7 ---
    [
        'title' => "Companies Pivot from Chatbots to 'Agentic AI' at Expo 2026",
        'desc' => "FedEx and Barclays are testing 'Agentic AI' systems that execute complex tasks like logistics and returns management.",
        'url' => "https://www.artificialintelligence-news.com/",
        'cat' => "Tech",
        'date' => "2026-02-07 10:00:00",
        'sentiment' => 'positive',
        'score' => 0.65
    ],
    // --- Feb 6 ---
    [
        'title' => "February FinTech Funding Off to Flying Start with Over $1Bn Raised",
        'desc' => "Over $1.02 billion was raised across 29 rounds in just one week, with companies like Talos and Varo Bank leading.",
        'url' => "https://fintech.global/2026/02/06/february-fintech-funding-off-to-flying-start-with-over-1bn-raised/",
        'cat' => "Finance",
        'date' => "2026-02-06 14:00:00",
        'sentiment' => 'positive',
        'score' => 0.7
    ],
    // --- Feb 5 ---
    [
        'title' => "Oracle Announces Massive $50B Expansion for AI Global Data Centers",
        'desc' => "Oracle vision for a global network of data centers specifically for generative AI triggered a staggering $50 billion investment plan.",
        'url' => "https://www.crescendo.ai/news/latest-ai-news-and-updates",
        'cat' => "Tech",
        'date' => "2026-02-05 16:00:00",
        'sentiment' => 'positive',
        'score' => 0.8
    ]
];

echo "Briefly.ai - Seeding demo stories...\n";
$stmt = $db->prepare("INSERT INTO articles 
    (title, ai_summary, original_url, category, sentiment, sentiment_score, fetched_at, is_processed, read_time_sec)
    VALUES (:title, :ai_summary, :url, :cat, :sent, :score, :date, 1, :read)");

foreach ($stories as $s) {
    $stmt->execute([
        ':title' => $s['title'],
        ':ai_summary' => $s['desc'],
        ':url' => $s['url'],
        ':cat' => $s['cat'],
        ':sent' => $s['sentiment'],
        ':score' => $s['score'],
        ':date' => $s['date'],
        ':read' => rand(45, 95)
    ]);
    echo "Inserted: " . $s['title'] . "\n";
}

echo "\nDone! Client demo news is now in the database.\n";
