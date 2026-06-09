-- ── Customer account status (active / blocked) ──
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'status');
SET @sql := IF(@col = 0,
    "ALTER TABLE customers ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER membership, ADD INDEX idx_cust_status (status)",
    'SELECT "customers.status already exists"');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
UPDATE customers SET status = 'active' WHERE status IS NULL OR status = '';
