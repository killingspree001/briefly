USE `test`;

-- Delete existing tables to start fresh and fix the columns
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `fetch_log`;

-- Articles Table (Matches exactly what index.php, fetch.php, and archive.php use)
CREATE TABLE `articles` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`             VARCHAR(500)  NOT NULL,
    `original_url`      TEXT          NOT NULL,
    `source_name`       VARCHAR(255)  DEFAULT NULL,
    `image_url`         TEXT          DEFAULT NULL,
    `description`       TEXT          DEFAULT NULL,
    `ai_summary`        TEXT          DEFAULT NULL,
    `category`          VARCHAR(50)   DEFAULT 'general',
    `sentiment`         VARCHAR(20)   DEFAULT 'neutral',
    `sentiment_score`   DECIMAL(3,2)  DEFAULT 0.00,
    `read_time_sec`     INT           DEFAULT 0,
    `is_processed`      TINYINT(1)    DEFAULT 0,
    `published_at`      TIMESTAMP     NULL DEFAULT NULL,
    `fetched_at`        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_fetched_at` (`fetched_at`),
    INDEX `idx_is_processed` (`is_processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fetch Log Table (Matches exactly what index.php and fetch.php use)
CREATE TABLE `fetch_log` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category`          VARCHAR(100)  DEFAULT NULL,
    `status`            VARCHAR(20)   NOT NULL, -- 'success', 'error'
    `articles_found`    INT           DEFAULT 0,
    `error_message`     TEXT          DEFAULT NULL,
    `run_at`            TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
