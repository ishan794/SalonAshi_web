-- ── REST API bearer tokens ──
-- SHA-256-hashed tokens for mobile/API clients. Plaintext never stored.
-- Additive & idempotent (CREATE TABLE IF NOT EXISTS).
CREATE TABLE IF NOT EXISTS `user_api_tokens` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       BIGINT UNSIGNED NOT NULL,
    `token_hash`    VARCHAR(64)     NOT NULL,       -- SHA-256(raw_token)
    `name`          VARCHAR(100)    NULL DEFAULT NULL, -- device label e.g. "iPhone 15"
    `last_used_at`  DATETIME        NULL DEFAULT NULL,
    `expires_at`    DATETIME        NOT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_token_hash` (`token_hash`),
    KEY `idx_user_tokens` (`user_id`),
    CONSTRAINT `fk_api_token_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
