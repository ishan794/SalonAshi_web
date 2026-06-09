-- ── Reviews & ratings ──
-- Captures in-app reviews (from booking-confirmation flow), manual entries by admin, and imported Google reviews.

CREATE TABLE IF NOT EXISTS reviews (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id          INT UNSIGNED NULL,
    appointment_id       INT UNSIGNED NULL,
    staff_id             INT UNSIGNED NULL,
    reviewer_name        VARCHAR(120) NOT NULL,
    reviewer_avatar_url  VARCHAR(255) NULL,
    rating               TINYINT UNSIGNED NOT NULL,
    title                VARCHAR(180) NULL,
    body                 TEXT NOT NULL,
    source               VARCHAR(20)  NOT NULL DEFAULT 'in-app',  -- in-app | google | manual
    source_id            VARCHAR(160) NULL,                       -- google review identifier
    source_url           VARCHAR(255) NULL,
    status               VARCHAR(20)  NOT NULL DEFAULT 'pending', -- pending | approved | rejected
    is_featured          TINYINT(1)   NOT NULL DEFAULT 0,
    source_created_at    DATETIME NULL,
    admin_response       TEXT NULL,
    created_at           DATETIME NULL,
    updated_at           DATETIME NULL,
    KEY idx_reviews_status_created (status, created_at),
    KEY idx_reviews_source         (source, source_id),
    KEY idx_reviews_rating_created (rating, created_at),
    KEY idx_reviews_featured       (is_featured, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
