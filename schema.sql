-- Briefly.ai - Database Schema
-- ===============================

-- Use the default TiDB Serverless database
USE `test`;

-- Articles Table
CREATE TABLE IF NOT EXISTS `articles` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fetch Log Table
CREATE TABLE IF NOT EXISTS `fetch_log` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `status`            VARCHAR(20)   NOT NULL,
    `articles_fetched`  INT           DEFAULT 0,
    `error_message`     TEXT          DEFAULT NULL,
    `created_at`        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
