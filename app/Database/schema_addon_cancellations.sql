-- SalonCMS — cancellation tracking add-on (idempotent)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS appointment_cancellations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id BIGINT UNSIGNED NOT NULL,
  customer_id BIGINT UNSIGNED NOT NULL,
  type ENUM('cancelled','no_show') NOT NULL,
  cancelled_by ENUM('customer','staff','system') NOT NULL DEFAULT 'customer',
  scheduled_at DATETIME NOT NULL,
  cancelled_at DATETIME NOT NULL,
  notice_hours DECIMAL(7,2) NULL COMMENT 'hours of notice; negative = after scheduled time (no-show)',
  reason TEXT NULL,
  fee_charged DECIMAL(10,2) NOT NULL DEFAULT 0,
  recorded_by BIGINT UNSIGNED NULL,
  created_at DATETIME NULL,
  KEY idx_ac_customer (customer_id),
  KEY idx_ac_appt (appointment_id),
  KEY idx_ac_type (type),
  KEY idx_ac_cancelled_at (cancelled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
