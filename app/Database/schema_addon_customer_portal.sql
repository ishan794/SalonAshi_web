-- ── Customer portal: OTP-based login ──
-- Customers identify themselves with their mobile number; we email a 6-digit code to their stored email,
-- then store the customer_id in the session.

CREATE TABLE IF NOT EXISTS customer_otps (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT UNSIGNED NOT NULL,
    code         VARCHAR(10)  NOT NULL,
    expires_at   DATETIME     NOT NULL,
    used_at      DATETIME     NULL,
    ip_address   VARCHAR(64)  NULL,
    created_at   DATETIME     NULL,
    KEY idx_cust_otp_customer (customer_id),
    KEY idx_cust_otp_expires  (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
