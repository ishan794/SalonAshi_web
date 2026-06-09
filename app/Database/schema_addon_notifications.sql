-- ── Centralized notifications + activity log ──

-- Per-user in-app notifications. user_id = NULL means broadcast to all admins.
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,                -- target staff user (null = broadcast)
    type        VARCHAR(50)  NOT NULL DEFAULT 'info',
    title       VARCHAR(180) NOT NULL,
    body        TEXT NULL,
    link        VARCHAR(255) NULL,
    icon        VARCHAR(60)  NULL,                -- lucide icon name
    color       VARCHAR(20)  NOT NULL DEFAULT 'gray',  -- gray|brand|amber|green|red|blue|purple
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    read_at     DATETIME NULL,
    created_at  DATETIME NULL,
    KEY idx_notif_user_read    (user_id, is_read, created_at),
    KEY idx_notif_created      (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only audit trail of significant actions.
CREATE TABLE IF NOT EXISTS system_logs (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NULL,
    user_name     VARCHAR(120) NULL,           -- snapshot at log time
    action        VARCHAR(80)  NOT NULL,       -- e.g. login.success, appointment.create, invoice.payment, settings.save
    entity_type   VARCHAR(60)  NULL,           -- e.g. appointment, invoice, customer, user, setting
    entity_id     INT UNSIGNED NULL,
    description   VARCHAR(255) NULL,
    ip_address    VARCHAR(64)  NULL,
    user_agent    VARCHAR(255) NULL,
    payload_json  TEXT NULL,
    severity      VARCHAR(20)  NOT NULL DEFAULT 'info',  -- info | warning | error
    created_at    DATETIME NULL,
    KEY idx_log_user      (user_id, created_at),
    KEY idx_log_action    (action, created_at),
    KEY idx_log_entity    (entity_type, entity_id),
    KEY idx_log_severity  (severity, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
