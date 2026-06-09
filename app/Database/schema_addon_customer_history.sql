-- ── Customer service history & records ──
-- Per-visit treatment records (notes, products, photos, ratings, hair formulas, allergies, preferences, files).

-- A row per completed service line. Auto-populated when an appointment moves to `completed`.
CREATE TABLE IF NOT EXISTS customer_service_history (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id       INT UNSIGNED NOT NULL,
    appointment_id    INT UNSIGNED NULL,
    service_id        INT UNSIGNED NULL,
    service_name      VARCHAR(160) NOT NULL,
    staff_id          INT UNSIGNED NULL,
    staff_name        VARCHAR(160) NULL,
    branch_id         INT UNSIGNED NULL,
    service_date      DATETIME NOT NULL,
    duration_min      INT NOT NULL DEFAULT 0,
    price             DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes             TEXT NULL,                -- per-visit stylist notes
    product_used      TEXT NULL,
    formula           TEXT NULL,                -- colour formula etc
    before_image      VARCHAR(255) NULL,
    after_image       VARCHAR(255) NULL,
    rating            TINYINT UNSIGNED NULL,
    invoice_id        INT UNSIGNED NULL,
    payment_status    VARCHAR(20)  NULL,
    created_at        DATETIME NULL,
    updated_at        DATETIME NULL,
    KEY idx_csh_customer (customer_id, service_date),
    KEY idx_csh_appt     (appointment_id),
    KEY idx_csh_staff    (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Free-form notes (separate from per-visit notes so they're searchable + filterable).
-- note_type: general | hair | skin | recommendation | formula | warning
CREATE TABLE IF NOT EXISTS customer_notes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT UNSIGNED NOT NULL,
    staff_id      INT UNSIGNED NULL,
    staff_name    VARCHAR(160) NULL,
    note_type     VARCHAR(40)  NOT NULL DEFAULT 'general',
    title         VARCHAR(180) NULL,
    body          TEXT NOT NULL,
    is_pinned     TINYINT(1) NOT NULL DEFAULT 0,
    created_at    DATETIME NULL,
    updated_at    DATETIME NULL,
    KEY idx_cn_customer (customer_id, is_pinned, created_at),
    KEY idx_cn_type     (note_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medical / allergy warnings — surfaced as a banner on the customer profile.
-- severity: mild | moderate | severe
CREATE TABLE IF NOT EXISTS customer_allergies (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT UNSIGNED NOT NULL,
    allergy_name  VARCHAR(160) NOT NULL,
    severity      VARCHAR(20)  NOT NULL DEFAULT 'mild',
    notes         TEXT NULL,
    created_at    DATETIME NULL,
    updated_at    DATETIME NULL,
    KEY idx_ca_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generic key/value preferences (preferred drink, music, stylist, products, etc).
CREATE TABLE IF NOT EXISTS customer_preferences (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id       INT UNSIGNED NOT NULL,
    preference_key    VARCHAR(80)  NOT NULL,
    preference_value  TEXT NOT NULL,
    created_at        DATETIME NULL,
    updated_at        DATETIME NULL,
    UNIQUE KEY uq_cp_cust_key (customer_id, preference_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uploaded files (consent forms, reference images, contracts).
CREATE TABLE IF NOT EXISTS customer_files (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT UNSIGNED NOT NULL,
    file_name     VARCHAR(255) NOT NULL,
    file_path     VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(120) NULL,
    size_bytes    INT UNSIGNED NULL,
    label         VARCHAR(180) NULL,
    uploaded_by   INT UNSIGNED NULL,
    created_at    DATETIME NULL,
    KEY idx_cf_customer (customer_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
