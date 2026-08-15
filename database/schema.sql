-- ============================================================
-- PMS — Project Management System
-- Database Schema (MySQL 5.7+/8.0, InnoDB, utf8mb4)
-- Import this file via phpMyAdmin or:
--   mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS pms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pms_db;

-- ------------------------------------------------------------
-- USERS / TEAM
-- ------------------------------------------------------------
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(150)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('admin','manager','member') NOT NULL DEFAULT 'member',
    department      VARCHAR(100)    NULL,
    job_title       VARCHAR(100)    NULL,
    phone           VARCHAR(30)     NULL,
    avatar_color    VARCHAR(7)      DEFAULT '#E8A33D',
    status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PROJECTS
-- ------------------------------------------------------------
CREATE TABLE projects (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200)    NOT NULL,
    code            VARCHAR(20)     NOT NULL UNIQUE,
    description     TEXT            NULL,
    project_type    VARCHAR(60)     NULL,          -- software, construction, research, ...
    status          ENUM('planning','active','on_hold','completed','cancelled') NOT NULL DEFAULT 'planning',
    priority        ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    progress        TINYINT UNSIGNED NOT NULL DEFAULT 0,   -- 0-100
    start_date      DATE            NULL,
    end_date        DATE            NULL,
    owner_id        INT UNSIGNED    NULL,
    created_by      INT UNSIGNED    NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    CONSTRAINT fk_projects_owner  FOREIGN KEY (owner_id)   REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Project team membership (many-to-many)
CREATE TABLE project_members (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    role_in_project VARCHAR(60)  NULL,
    added_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_user (project_id, user_id),
    CONSTRAINT fk_pm_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_pm_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PROJECT PHASES (milestones/stages of a project)
-- ------------------------------------------------------------
CREATE TABLE phases (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      INT UNSIGNED NOT NULL,
    name            VARCHAR(150) NOT NULL,
    sequence_order  SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    status          ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
    start_date      DATE NULL,
    end_date        DATE NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_phase_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TASKS (supports subtasks via parent_task_id)
-- ------------------------------------------------------------
CREATE TABLE tasks (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id          INT UNSIGNED NOT NULL,
    phase_id            INT UNSIGNED NULL,
    parent_task_id      INT UNSIGNED NULL,          -- NULL = top-level task
    title               VARCHAR(200) NOT NULL,
    description         TEXT NULL,
    acceptance_criteria TEXT NULL,
    priority            ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    status              ENUM('todo','in_progress','review','done','blocked') NOT NULL DEFAULT 'todo',
    complexity          ENUM('simple','moderate','complex') NOT NULL DEFAULT 'moderate',
    progress            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    estimated_hours     DECIMAL(6,2) NULL,
    actual_hours        DECIMAL(6,2) NULL,
    assigned_to         INT UNSIGNED NULL,
    reviewer_id         INT UNSIGNED NULL,
    start_date          DATE NULL,
    due_date            DATE NULL,
    completed_at        DATETIME NULL,
    created_by          INT UNSIGNED NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          DATETIME NULL,
    CONSTRAINT fk_task_project  FOREIGN KEY (project_id)     REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_phase    FOREIGN KEY (phase_id)       REFERENCES phases(id)   ON DELETE SET NULL,
    CONSTRAINT fk_task_parent   FOREIGN KEY (parent_task_id) REFERENCES tasks(id)    ON DELETE CASCADE,
    CONSTRAINT fk_task_assignee FOREIGN KEY (assigned_to)    REFERENCES users(id)    ON DELETE SET NULL,
    CONSTRAINT fk_task_reviewer FOREIGN KEY (reviewer_id)    REFERENCES users(id)    ON DELETE SET NULL,
    CONSTRAINT fk_task_creator  FOREIGN KEY (created_by)     REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- Task dependencies (many-to-many: task depends on another task)
CREATE TABLE task_dependencies (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id             INT UNSIGNED NOT NULL,
    depends_on_task_id  INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_dependency (task_id, depends_on_task_id),
    CONSTRAINT fk_dep_task    FOREIGN KEY (task_id)            REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_dep_depends FOREIGN KEY (depends_on_task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Task comments
CREATE TABLE task_comments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id     INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    comment     TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Task attachments (files stored on disk; this table stores metadata only)
CREATE TABLE attachments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id         INT UNSIGNED NULL,
    project_id      INT UNSIGNED NULL,
    uploaded_by     INT UNSIGNED NULL,
    original_name   VARCHAR(255) NOT NULL,
    stored_path     VARCHAR(500) NOT NULL,
    file_size       INT UNSIGNED NULL,
    mime_type       VARCHAR(100) NULL,
    uploaded_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_task    FOREIGN KEY (task_id)    REFERENCES tasks(id)    ON DELETE CASCADE,
    CONSTRAINT fk_att_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_user    FOREIGN KEY (uploaded_by) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ACTIVITY LOG (audit trail across the system)
-- ------------------------------------------------------------
CREATE TABLE activity_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NULL,
    entity_type     VARCHAR(50)  NOT NULL,   -- project, task, user, phase ...
    entity_id       INT UNSIGNED NULL,
    action          VARCHAR(50)  NOT NULL,   -- created, updated, deleted, status_changed ...
    description     VARCHAR(500) NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- INDEXES for common query patterns
-- ------------------------------------------------------------
CREATE INDEX idx_tasks_project      ON tasks(project_id);
CREATE INDEX idx_tasks_assigned     ON tasks(assigned_to);
CREATE INDEX idx_tasks_status       ON tasks(status);
CREATE INDEX idx_tasks_parent       ON tasks(parent_task_id);
CREATE INDEX idx_projects_status    ON projects(status);
CREATE INDEX idx_activity_entity    ON activity_log(entity_type, entity_id);
CREATE INDEX idx_activity_created   ON activity_log(created_at);

-- ------------------------------------------------------------
-- SEED DATA
-- NOTE: No admin user or sample data is inserted here on purpose.
-- After importing this schema, open install.php in your browser once.
-- It creates the admin account (hashing the password securely with
-- PHP's own password_hash()) and a sample project so you can explore
-- the system immediately. It disables itself after running.
-- ------------------------------------------------------------
