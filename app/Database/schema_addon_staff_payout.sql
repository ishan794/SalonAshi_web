-- ── Staff payout & bank details ──
-- Adds nullable columns to the staff table for payout method/frequency and bank
-- account details. Additive & idempotent — no existing data is changed or deleted.

-- bank_name
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='bank_name');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN bank_name VARCHAR(150) NULL DEFAULT NULL AFTER commission_pct", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- bank_account_name
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='bank_account_name');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN bank_account_name VARCHAR(150) NULL DEFAULT NULL AFTER bank_name", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- bank_account_no
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='bank_account_no');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN bank_account_no VARCHAR(50) NULL DEFAULT NULL AFTER bank_account_name", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- bank_branch
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='bank_branch');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN bank_branch VARCHAR(150) NULL DEFAULT NULL AFTER bank_account_no", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- bank_code (bank/branch SLIPS code)
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='bank_code');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN bank_code VARCHAR(30) NULL DEFAULT NULL AFTER bank_branch", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- payout_method
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='payout_method');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN payout_method VARCHAR(20) NULL DEFAULT NULL AFTER bank_code", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- payout_frequency
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='payout_frequency');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN payout_frequency VARCHAR(20) NULL DEFAULT NULL AFTER payout_method", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- payout_notes
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME='payout_notes');
SET @s := IF(@c=0, "ALTER TABLE staff ADD COLUMN payout_notes VARCHAR(255) NULL DEFAULT NULL AFTER payout_frequency", 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
