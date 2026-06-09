-- ── Appointment reminders ──
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'reminded_at');
SET @sql := IF(@col = 0,
    'ALTER TABLE appointments ADD COLUMN reminded_at DATETIME NULL AFTER status, ADD INDEX idx_appt_reminded (reminded_at)',
    'SELECT "appointments.reminded_at already exists"');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
