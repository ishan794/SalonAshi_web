-- ── Payment receipt attachment (e.g. bank-transfer slip) ──
-- Adds a nullable column to store the uploaded receipt filename. Additive & idempotent.
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'receipt_path');
SET @sql := IF(@col = 0,
    "ALTER TABLE payments ADD COLUMN receipt_path VARCHAR(255) NULL DEFAULT NULL AFTER txn_ref",
    'SELECT "payments.receipt_path already exists"');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
