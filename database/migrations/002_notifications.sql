-- ============================================================
-- Migration 002 — Internal notifications (Phase 6)
-- Run via phpMyAdmin (SQL tab) or:
--   mysql -u root -p pms_db < database/migrations/002_notifications.sql
-- ============================================================
USE pms_db;

CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,       -- recipient
    actor_id    INT UNSIGNED NULL,           -- who triggered it (nullable = system)
    type        VARCHAR(40) NOT NULL,        -- task_assigned, task_commented, task_due_soon ...
    message     VARCHAR(255) NOT NULL,
    link        VARCHAR(255) NULL,           -- relative app URL to open on click
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user  FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read);
