-- ── Staff password reset tokens ──
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    token       VARCHAR(80)  NOT NULL,
    expires_at  DATETIME     NOT NULL,
    used_at     DATETIME     NULL,
    ip_address  VARCHAR(64)  NULL,
    created_at  DATETIME     NULL,
    KEY idx_pr_token   (token),
    KEY idx_pr_user    (user_id),
    KEY idx_pr_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
