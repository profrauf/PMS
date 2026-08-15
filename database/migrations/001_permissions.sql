-- ============================================================
-- Migration 001 — Fine-grained permissions (Phase 5)
-- Run this once via phpMyAdmin (SQL tab) or:
--   mysql -u root -p pms_db < database/migrations/001_permissions.sql
-- Safe to run on an existing pms_db — does not touch existing data.
-- ============================================================
USE pms_db;

CREATE TABLE IF NOT EXISTS permissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`       VARCHAR(60) NOT NULL UNIQUE,
    label       VARCHAR(150) NOT NULL,
    category    VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('admin','manager','member') NOT NULL,
    permission_key  VARCHAR(60) NOT NULL,
    allowed         TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_role_permission (role, permission_key),
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_key) REFERENCES permissions(`key`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seed permission catalogue
-- ------------------------------------------------------------
INSERT IGNORE INTO permissions (`key`, label, category) VALUES
    ('projects.create', 'Create projects',            'Projects'),
    ('projects.edit',   'Edit projects',               'Projects'),
    ('projects.delete', 'Delete projects',             'Projects'),
    ('tasks.create',    'Create tasks',                'Tasks'),
    ('tasks.edit',      'Edit tasks',                  'Tasks'),
    ('tasks.delete',    'Delete tasks',                'Tasks'),
    ('team.manage',     'Add / edit / remove members', 'Team'),
    ('reports.view',    'View reports',                'Reports'),
    ('settings.manage', 'Manage system settings',      'Settings');

-- ------------------------------------------------------------
-- Seed defaults that reproduce the EXACT behavior the app had
-- before this migration (nothing changes for existing users
-- until an admin edits the matrix from Settings → Permissions).
-- ------------------------------------------------------------
INSERT IGNORE INTO role_permissions (role, permission_key, allowed) VALUES
    -- admin: everything
    ('admin','projects.create',1), ('admin','projects.edit',1), ('admin','projects.delete',1),
    ('admin','tasks.create',1),    ('admin','tasks.edit',1),    ('admin','tasks.delete',1),
    ('admin','team.manage',1),     ('admin','reports.view',1),  ('admin','settings.manage',1),

    -- manager: everything except system settings
    ('manager','projects.create',1), ('manager','projects.edit',1), ('manager','projects.delete',1),
    ('manager','tasks.create',1),    ('manager','tasks.edit',1),    ('manager','tasks.delete',1),
    ('manager','team.manage',1),     ('manager','reports.view',1),  ('manager','settings.manage',0),

    -- member: matches old behavior — could create/edit/delete tasks,
    -- could create/edit projects but NOT delete them, no team/settings management
    ('member','projects.create',1), ('member','projects.edit',1), ('member','projects.delete',0),
    ('member','tasks.create',1),    ('member','tasks.edit',1),    ('member','tasks.delete',1),
    ('member','team.manage',0),     ('member','reports.view',1),  ('member','settings.manage',0);
