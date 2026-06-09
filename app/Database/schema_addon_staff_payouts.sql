-- ── Recorded staff payouts ──
-- Persists actual payouts made to stylists (amount, method, slip, status, notify).
-- Additive & idempotent — creates the table only if missing.
CREATE TABLE IF NOT EXISTS staff_payouts (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  staff_id        BIGINT UNSIGNED NOT NULL,
  period_from     DATE NULL,
  period_to       DATE NULL,
  gross_revenue   DECIMAL(12,2) NOT NULL DEFAULT 0,
  commission_pct  DECIMAL(5,2)  NOT NULL DEFAULT 0,
  amount          DECIMAL(12,2) NOT NULL DEFAULT 0,
  method          VARCHAR(20) NULL,
  reference       VARCHAR(100) NULL,
  slip_path       VARCHAR(255) NULL,
  notes           VARCHAR(500) NULL,
  status          VARCHAR(20) NOT NULL DEFAULT 'pending',
  notified_at     DATETIME NULL,
  paid_at         DATETIME NULL,
  created_by      BIGINT UNSIGNED NULL,
  created_at      DATETIME NULL,
  updated_at      DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_sp_staff (staff_id),
  KEY idx_sp_period (period_from, period_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
