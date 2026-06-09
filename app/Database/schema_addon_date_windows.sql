-- SalonCMS — per-date working windows (idempotent)
-- A staff member can have one or more time windows on a specific date.
-- When rows exist for (staff_id, on_date), they REPLACE the weekly schedule for that date.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS staff_date_windows (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id BIGINT UNSIGNED NOT NULL,
  on_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  note VARCHAR(180) NULL,
  created_at DATETIME NULL,
  KEY idx_sdw_staff_date (staff_id, on_date),
  KEY idx_sdw_date (on_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
