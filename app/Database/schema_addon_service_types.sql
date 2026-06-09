-- ── Service types (Normal / Emergency / VIP etc) ──
-- Each appointment_service line picks one type, and the line price = service.price * type.multiplier.
-- The default type ("Standard") gets multiplier=1.00 and is auto-applied for existing rows.

CREATE TABLE IF NOT EXISTS service_types (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(80)  NOT NULL,
    slug          VARCHAR(80)  NOT NULL UNIQUE,
    color         VARCHAR(20)  NOT NULL DEFAULT 'gray',    -- tailwind color name (gray, red, amber, blue, green, brand)
    multiplier    DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    description   VARCHAR(255) NULL,
    is_default    TINYINT(1)   NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order    INT          NOT NULL DEFAULT 0,
    created_at    DATETIME NULL,
    updated_at    DATETIME NULL,
    KEY idx_st_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common types
INSERT IGNORE INTO service_types (name, slug, color, multiplier, description, is_default, is_active, sort_order, created_at, updated_at) VALUES
('Standard',  'standard',  'gray',  1.00, 'Regular service at standard pricing.',           1, 1, 1, NOW(), NOW()),
('Emergency', 'emergency', 'red',   1.50, 'Walk-in / urgent service — 50% surcharge.',      0, 1, 2, NOW(), NOW()),
('VIP',       'vip',       'brand', 1.25, 'Premium service tier with priority handling.',   0, 1, 3, NOW(), NOW());

-- Tag every existing appointment_service line with the default Standard type.
-- (Portable: query information_schema to test for column existence first.)
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointment_services' AND COLUMN_NAME = 'service_type_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE appointment_services ADD COLUMN service_type_id INT UNSIGNED NULL AFTER service_id, ADD INDEX idx_appsvc_type (service_type_id)',
    'SELECT "appointment_services.service_type_id already exists, skipping"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE appointment_services
   SET service_type_id = (SELECT id FROM service_types WHERE slug = 'standard' LIMIT 1)
 WHERE service_type_id IS NULL;
