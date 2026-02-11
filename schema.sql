-- =============================================
-- Briefly.ai — Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS `briefly_ai`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `briefly_ai`;

-- ─── Articles Table ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `articles` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(500)   NOT NULL,
    `original_url`    TEXT           NOT NULL,
    `source_name`     VARCHAR(255)   DEFAULT NULL,
    `image_url`       TEXT           DEFAULT NULL,
    `description`     TEXT           DEFAULT NULL,
    `ai_summary`      TEXT           DEFAULT NULL,
    `category`        VARCHAR(50)    NOT NULL DEFAULT 'general',
    `sentiment`       VARCHAR(20)    DEFAULT 'neutral',
    `sentiment_score` DECIMAL(4,2)   DEFAULT 0.00,
    `read_time_sec`   INT UNSIGNED   DEFAULT 60,
    `published_at`    DATETIME       DEFAULT NULL,
    `fetched_at`      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `is_processed`    TINYINT(1)     DEFAULT 0,

    -- Prevent duplicate articles
    UNIQUE KEY `uq_url` (`original_url`(500)),

    -- Fast lookups
    INDEX `idx_category`     (`category`),
    INDEX `idx_processed`    (`is_processed`),
    INDEX `idx_published`    (`published_at` DESC),
    INDEX `idx_sentiment`    (`sentiment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Fetch Log (tracks harvester runs) ──────────────────────────────
CREATE TABLE IF NOT EXISTS `fetch_log` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `run_at`         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `category`       VARCHAR(50)  NOT NULL,
    `articles_found` INT UNSIGNED DEFAULT 0,
    `status`         VARCHAR(20)  DEFAULT 'success',
    `error_message`  TEXT         DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
