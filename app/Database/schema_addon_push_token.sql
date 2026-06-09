-- ── Expo push token for mobile devices ──
-- Stores the per-user Expo push token so the backend can send push notifications.
-- Additive & idempotent.
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'expo_push_token');
SET @s := IF(@c = 0,
    "ALTER TABLE users ADD COLUMN expo_push_token VARCHAR(255) NULL DEFAULT NULL AFTER status",
    'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
