-- SalonCMS — staff schedule add-on (idempotent)
SET NAMES utf8mb4;

-- Per-day-of-week working hours
CREATE TABLE IF NOT EXISTS staff_schedule (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id BIGINT UNSIGNED NOT NULL,
  dow TINYINT UNSIGNED NOT NULL COMMENT '0=Sun, 1=Mon, … 6=Sat',
  start_time TIME NULL,
  end_time TIME NULL,
  is_off TINYINT(1) NOT NULL DEFAULT 0,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_staff_dow (staff_id, dow)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Specific dates the staff is off (vacation, sick, etc.)
CREATE TABLE IF NOT EXISTS staff_time_off (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id BIGINT UNSIGNED NOT NULL,
  off_date DATE NOT NULL,
  reason VARCHAR(180) NULL,
  created_at DATETIME NULL,
  UNIQUE KEY uniq_staff_date (staff_id, off_date),
  KEY idx_off_date (off_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
