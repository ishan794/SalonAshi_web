-- SalonCMS — loyalty add-on (idempotent)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS loyalty_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  type ENUM('earn','redeem','adjust','expire') NOT NULL,
  points INT NOT NULL COMMENT 'positive=credit, negative=debit',
  invoice_id BIGINT UNSIGNED NULL,
  note VARCHAR(255) NULL,
  balance_after INT NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  KEY idx_lt_customer (customer_id),
  KEY idx_lt_invoice (invoice_id),
  KEY idx_lt_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default loyalty settings if not present
INSERT IGNORE INTO settings (k, v, updated_at) VALUES
  ('loyalty_enabled',         '1',     NOW()),
  ('loyalty_earn_per_lkr',    '0.01',  NOW()),    -- 1 point per 100 LKR spent
  ('loyalty_redeem_value',    '0.5',   NOW()),    -- 1 point = 0.5 LKR off
  ('loyalty_min_redeem_pts',  '50',    NOW()),    -- need at least 50 points to redeem
  ('loyalty_tier_silver_pts', '500',   NOW()),
  ('loyalty_tier_gold_pts',   '1500',  NOW()),
  ('loyalty_tier_platinum_pts','5000', NOW()),
  ('loyalty_tier_silver_disc',   '5',  NOW()),
  ('loyalty_tier_gold_disc',     '10', NOW()),
  ('loyalty_tier_platinum_disc', '15', NOW());
