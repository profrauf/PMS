# PMS — Project Management System

A PHP/MySQL project management system designed for local XAMPP deployment, with project and task management, team roles, granular permissions, internal notifications, reporting, attachments, activity logging, and database backup support.

## Table of Contents

* [Quick Start](#quick-start)
* [Project Overview](#project-overview)
* [Key Features](#key-features)
* [Tech Stack](#tech-stack)
* [Requirements](#requirements)
* [Installation](#installation)
* [Database Setup](#database-setup)
* [Environment Configuration](#environment-configuration)
* [First-Time Setup](#first-time-setup)
* [Existing Installation Upgrade](#existing-installation-upgrade)
* [Login](#login)
* [Project Structure](#project-structure)
* [Architecture](#architecture)
* [Feature Breakdown](#feature-breakdown)
* [Roles and Permissions](#roles-and-permissions)
* [Notifications](#notifications)
* [Reports and Analytics](#reports-and-analytics)
* [Development Milestones](#development-milestones)
* [Production Readiness](#production-readiness)
* [Database Backup](#database-backup)
* [Security Notes](#security-notes)
* [Project Status](#project-status)
* [Future Enhancements](#future-enhancements)
* [Operational Notes](#operational-notes)
* [License](#license)

## Quick Start

For a new local installation:

```text
Copy project
→ Start Apache + MySQL
→ Import database/schema.sql
→ Run database migrations in order
→ Open install.php
→ Create Administrator account
→ Login
```

### Windows

```text
C:\xampp\htdocs\pms
```

### macOS

```text
/Applications/XAMPP/htdocs/pms
```

Then start **Apache** and **MySQL** from the XAMPP Control Panel.

---

## Project Overview

**PMS — Project Management System** is a PHP/MySQL application intended for managing projects, tasks, teams, permissions, attachments, activities, notifications, and project-related reporting.

The system uses a modular PHP structure with a **Repository Pattern** to isolate database access from request-handling logic.

The application is designed primarily for local operation with **XAMPP** and can be prepared for deployment to a real hosting environment through environment-based configuration and security hardening.

---

## Key Features

| Area               | Features                                                      |
| ------------------ | ------------------------------------------------------------- |
| Authentication     | Login and Administrator account setup                         |
| User Roles         | `admin`, `manager`, `member`                                  |
| Project Management | Project CRUD and project-related workflows                    |
| Task Management    | Task CRUD, assignments, dependencies, `actual_hours`          |
| Team Management    | Team/user management                                          |
| Dashboard          | Project and task overview                                     |
| Activity Logging   | Application activity tracking                                 |
| Attachments        | File uploads with extension and size restrictions             |
| Soft Delete        | Non-destructive deletion behavior where implemented           |
| Permissions        | Granular role-based permissions                               |
| Notifications      | Internal notifications and unread counter                     |
| Reports            | Gantt, Burndown, Team Performance                             |
| Export             | CSV export                                                    |
| Backup             | `mysqldump` + `gzip`, with retention of the latest 14 backups |

---

## Tech Stack

| Technology              | Purpose                         |
| ----------------------- | ------------------------------- |
| PHP                     | Application runtime             |
| MySQL / MariaDB         | Database                        |
| Apache                  | Web server                      |
| XAMPP                   | Local development environment   |
| HTML / CSS / JavaScript | Application interface           |
| PDO                     | Database access                 |
| Repository Pattern      | Database access isolation       |
| `.env`                  | Environment configuration       |
| `.htaccess`             | Access and execution protection |
| Bash                    | Backup scripting                |
| `mysqldump`             | Database backup                 |
| `gzip`                  | Backup compression              |

The project does not require Composer-based external libraries for the currently implemented CSV export and internal notification functionality.

---

## Requirements

Before installation, ensure the following are available:

* XAMPP with Apache and MySQL/MariaDB.
* PHP provided by the XAMPP installation.
* A web browser.
* Access to `phpMyAdmin`.
* Bash support for executing `scripts/backup.sh` when database backups are required.

---

## Installation

### 1. Copy the Project

Place the complete `pms` directory inside the XAMPP web root.

**Windows:**

```text
C:\xampp\htdocs\pms
```

**macOS:**

```text
/Applications/XAMPP/htdocs/pms
```

### 2. Start XAMPP Services

Start:

* Apache
* MySQL

from the XAMPP Control Panel.

### 3. Open phpMyAdmin

Open:

```text
http://localhost/phpmyadmin
```

### 4. Import the Database Schema

Execute:

```text
database/schema.sql
```

This creates:

```text
pms_db
```

### 5. Run the Initial Migrations

For a **first installation only**, execute the migrations in this exact order:

```text
database/migrations/001_permissions.sql
database/migrations/002_notifications.sql
```

Migration order matters because the database changes are incremental.

### 6. Configure the Environment

For an actual deployment environment, copy:

```text
.env.example
```

to:

```text
.env
```

Then adjust the configuration values inside `.env`.

### 7. Run the Installer

Open:

```text
http://localhost/pms/install.php
```

Create the initial Administrator account.

### 8. Login

Open:

```text
http://localhost/pms/modules/auth/login.php
```

There is **no default account**. The email address and password are defined during installation.

---

## Database Setup

The project uses a base schema followed by incremental database migrations.

### Base Schema

```text
database/schema.sql
```

The base schema creates the primary database structure and the:

```text
pms_db
```

database.

### Migration Order

| Migration               | Purpose                                   |
| ----------------------- | ----------------------------------------- |
| `001_permissions.sql`   | Permissions and role-permission structure |
| `002_notifications.sql` | Internal notifications structure          |

The migration sequence should be preserved.

---

## Environment Configuration

### Configuration Files

```text
.env.example
.env
config/env.php
config/database.php
```

### `.env.example`

Provides the configuration template for an environment.

### `.env`

Contains the actual environment-specific configuration and should **not** be committed to Git.

### `config/env.php`

Loads and manages environment configuration for the application.

### `config/database.php`

Reads the database configuration from the environment when `.env` is available. When it is not available, the project uses the default XAMPP database configuration.

### Default XAMPP Configuration

```env
DB_HOST=localhost
DB_NAME=pms_db
DB_USER=root
DB_PASS=
```

Using:

```text
DB_USER=root
DB_PASS=
```

is suitable only for a local development environment where this is the configured XAMPP database setup. It should not be treated as a secure production database configuration.

### Application Environment

The project also supports:

```env
APP_ENV=local
FORCE_HTTPS=0
```

For production-oriented deployment:

```env
APP_ENV=production
FORCE_HTTPS=1
```

`APP_ENV=production` causes application errors to be logged to:

```text
storage/logs/error.log
```

while:

```text
APP_ENV=local
```

allows errors to be displayed during local development.

---

## First-Time Setup

A new installation should follow this sequence:

```text
1. Copy the project into the XAMPP htdocs directory
2. Start Apache and MySQL
3. Open phpMyAdmin
4. Execute database/schema.sql
5. Execute 001_permissions.sql
6. Execute 002_notifications.sql
7. Configure .env when required
8. Open /pms/install.php
9. Create the Administrator account
10. Open /pms/modules/auth/login.php
```

### Installer Safety

After successful installation, it is preferable to **delete or rename `install.php`** to prevent unnecessary access to the installer.

---

## Existing Installation Upgrade

For an existing installation, **do not re-run `schema.sql`** when it has already been applied to the database.

Instead:

```text
Existing database
→ Run only the required new migrations
```

For example:

```text
database/migrations/001_permissions.sql
database/migrations/002_notifications.sql
```

should be executed only when those migrations have not already been applied.

This prevents unnecessary database recreation and reduces the risk of data loss or conflicts.

---

## Login

The login page is available at:

```text
http://localhost/pms/modules/auth/login.php
```

There is no predefined default account.

Administrator credentials are created during:

```text
http://localhost/pms/install.php
```

---

## Project Structure

```text
pms/
├── .env.example
├── config/
│   ├── env.php
│   ├── database.php
│   └── app.php
├── includes/
│   ├── repositories/
│   ├── helpers.php
│   └── auth.php
├── modules/
│   ├── auth/
│   ├── dashboard/
│   ├── projects/
│   ├── tasks/
│   ├── team/
│   ├── reports/
│   ├── attachments/
│   ├── notifications/
│   └── settings/
├── assets/
│   └── css/
├── uploads/
├── storage/
│   ├── logs/
│   └── backups/
├── scripts/
│   └── backup.sh
├── database/
│   ├── schema.sql
│   └── migrations/
└── install.php
```

### Directory Responsibilities

| Path                     | Responsibility                                       |
| ------------------------ | ---------------------------------------------------- |
| `config/`                | Application, environment, and database configuration |
| `includes/repositories/` | Repository classes responsible for data access       |
| `includes/helpers.php`   | General reusable helper functions                    |
| `includes/auth.php`      | Authentication and authorization support             |
| `modules/auth/`          | Authentication functionality                         |
| `modules/dashboard/`     | Dashboard                                            |
| `modules/projects/`      | Project management                                   |
| `modules/tasks/`         | Task management                                      |
| `modules/team/`          | Team management                                      |
| `modules/reports/`       | Reporting and analytics                              |
| `modules/attachments/`   | Attachment handling                                  |
| `modules/notifications/` | Internal notifications                               |
| `modules/settings/`      | Application and permission settings                  |
| `assets/css/`            | Stylesheets                                          |
| `uploads/`               | Uploaded files                                       |
| `storage/logs/`          | Application logs                                     |
| `storage/backups/`       | Database backups                                     |
| `scripts/backup.sh`      | Database backup script                               |
| `database/`              | Schema and incremental migrations                    |
| `install.php`            | Initial application installation                     |

---

## Architecture

The application follows a modular structure based on:

**Thin Controllers + Repository Pattern**

### Request and Application Flow

```text
User Request
    ↓
modules/*
    ↓
Application / Authentication Logic
    ↓
Repositories
    ↓
PDO
    ↓
MySQL / MariaDB
```

### Responsibilities

#### `modules/*`

Responsible for request flow, module-specific application behavior, and user-facing functionality.

#### `repositories/*`

Responsible for database access and SQL operations.

The Repository Pattern isolates SQL from the controllers and reduces direct database logic inside request-handling code.

#### `helpers.php`

Contains reusable application-level helper functions.

#### `auth.php`

Handles authentication and authorization-related logic.

#### `config/app.php`

Acts as the application bootstrap/configuration entry point.

### Implemented Repositories

The project includes:

```text
ProjectRepository
TaskRepository
UserRepository
ActivityRepository
AttachmentRepository
PermissionRepository
NotificationRepository
```

Repository-based refactoring includes moving operations such as:

```text
logActivity()
attemptLogin()
```

into their corresponding repository-based architecture without changing user-facing behavior.

---

## Feature Breakdown

### Authentication and Users

The application includes:

* Authentication.
* Administrator account creation during installation.
* `admin`, `manager`, and `member` roles.
* Permission-aware actions.

### Projects

Project management includes CRUD operations and project-related workflows.

### Tasks

Task management includes:

* CRUD operations.
* Task assignment.
* Task dependencies.
* Soft Delete.
* `actual_hours`.
* My Tasks functionality.

### Attachments

Attachment handling includes:

* Whitelisted file extensions.
* Maximum file size of **15 MB**.
* Protection against PHP execution.

### Activity Log

The application records activity through the activity logging functionality.

### Dashboard

The dashboard provides an application overview for project and task management.

---

## Roles and Permissions

The permission model consists of:

```text
permissions
role_permissions
PermissionRepository
```

Permission checks are exposed through:

```php
userCan('key')
requirePermission('key')
```

### Role Behavior

| Role          | Permission Model               |
| ------------- | ------------------------------ |
| Administrator | Full permissions automatically |
| Manager       | Permissions can be customized  |
| Member        | Permissions can be customized  |

### Permission Management

Permission administration is available through:

```text
modules/settings/permissions.php
```

The interface also hides actions and controls that the current user is not permitted to perform.

A specific implemented permission includes:

```text
settings.manage
```

### Administrator Fail-Safe

Administrators automatically retain full access to the system's permissions.

The system also prevents deletion of the last Administrator account.

---

## Notifications

The project includes an internal notification system based on:

```text
notifications
NotificationRepository
```

Implemented functionality includes:

* Unread notification counter.
* Notification center/page.
* Notification when a task is assigned or reassigned.
* Notification when someone other than the assigned person adds a comment.

### Deadline Reminders

Deadline reminder automation is **not currently implemented**.

It requires scheduled execution through **Cron**.

---

## Reports and Analytics

The reporting module includes:

### Gantt

Provides Gantt-based project/task visualization.

### Burndown

Provides Burndown reporting.

### Team Performance

Provides team performance reporting.

### CSV Export

The project supports CSV export without requiring additional Composer-based PDF/Excel libraries.

### PDF and Excel

Native PDF and Excel export are **not implemented**.

Libraries such as TCPDF or PhpSpreadsheet would be required for those formats, but they are not currently part of the implemented project.

---

## Development Milestones

All seven planned development phases have been completed.

### Phase 1 — Foundation

Implemented:

* Authentication.
* `admin` / `manager` / `member`.
* Project CRUD.
* Task CRUD.
* Team CRUD.
* Dashboard.
* Activity Log.

### Phase 2 — Functional Completion

Implemented:

* Attachments.
* Whitelisted extensions.
* Maximum upload size of 15 MB.
* Protection against PHP execution.
* Task Dependencies.
* Soft Delete.
* Prevention of deleting the last Administrator.
* Prevention of self-deletion.
* My Tasks.
* `actual_hours`.

### Phase 3 — Reports & Analytics

Implemented:

* Gantt.
* Burndown.
* Team Performance.
* CSV Export.

Native PDF/Excel export remains outside the implemented scope and would require external libraries.

### Phase 4 — Repository Refactoring

Implemented:

* Repository Pattern.
* `ProjectRepository`.
* `TaskRepository`.
* `UserRepository`.
* `ActivityRepository`.
* `AttachmentRepository`.
* Thin Controllers.
* Repository-based handling for `logActivity()` and `attemptLogin()`.

The refactoring isolates SQL access without changing user-facing behavior.

### Phase 5 — Granular Permissions

Implemented:

* `permissions`.
* `role_permissions`.
* `PermissionRepository`.
* `userCan()`.
* `requirePermission()`.
* Administrator full-access fail-safe.
* Configurable Manager permissions.
* Configurable Member permissions.
* `settings.manage`.
* Permission administration at `modules/settings/permissions.php`.
* Permission-aware UI actions.

### Phase 6 — Internal Notifications

Implemented:

* Notification schema.
* `NotificationRepository`.
* Unread counter.
* Notification center.
* Assignment/reassignment notifications.
* Comment notifications when comments are added by someone other than the assigned user.

Deadline reminders are not implemented and require Cron-based automation.

### Phase 7 — Production Readiness

Implemented:

* `.env.example`.
* Environment loading through `config/env.php`.
* Database configuration through environment variables.
* `HttpOnly` session cookies.
* `SameSite=Lax`.
* `Secure` cookies when `FORCE_HTTPS=1`.
* Production error logging.
* Development error display.
* `storage/` protection through `.htaccess`.
* Database backup using `mysqldump` + `gzip`.
* Retention of the latest 14 backups.
* Backup preparation for Cron scheduling.
* Git exclusions for `.env`, uploads, logs, and backups.

---

## Production Readiness

The project includes a production-oriented foundation covering environment configuration, session hardening, error logging, storage protection, and database backup.

Production deployment should use environment-specific credentials rather than the default local XAMPP configuration.

### Environment Separation

Local development:

```env
APP_ENV=local
```

Production-oriented configuration:

```env
APP_ENV=production
```

The application changes its error-handling behavior according to the environment.

### HTTPS

Set:

```env
FORCE_HTTPS=1
```

when HTTPS is enabled and the deployment requires secure cookies.

When enabled, the session cookie uses the `Secure` attribute.

---

## Database Backup

Database backups are handled by:

```text
scripts/backup.sh
```

The script uses:

```text
mysqldump
gzip
```

to generate compressed database backups.

Backups are stored under:

```text
storage/backups/
```

The system retains the latest:

```text
14
```

backup copies.

The backup script is prepared for scheduling through **Cron**. The scheduling instructions are provided within the script itself.

---

## Security Notes

The project includes several application-level security measures.

### Session Security

Session cookies use:

```text
HttpOnly
SameSite=Lax
```

The:

```text
Secure
```

attribute is enabled when:

```env
FORCE_HTTPS=1
```

### Upload Security

Uploaded files are restricted through:

* Whitelisted extensions.
* Maximum size of 15 MB.
* Protection against PHP execution.

### Storage Protection

The:

```text
storage/
```

directory is protected through `.htaccess` to prevent direct access.

### Environment Protection

The actual environment file:

```text
.env
```

must not be committed to Git.

The `.gitignore` configuration also excludes:

```text
.env
uploads/
logs/
backups/
```

### Account Safety

The application prevents:

* Deletion of the last Administrator.
* Users deleting themselves.

---

## Project Status

### Current Status

**All seven planned phases are complete.**

The system is ready for **full internal operation**.

### Implemented

* Authentication.
* Roles and permissions.
* Project/task/team management.
* Dashboard.
* Activity logging.
* Attachments.
* Task dependencies.
* Soft Delete.
* My Tasks.
* `actual_hours`.
* Gantt.
* Burndown.
* Team Performance.
* CSV Export.
* Repository Pattern.
* Granular permissions.
* Internal notifications.
* Session hardening.
* Environment-based configuration.
* Error logging.
* Storage protection.
* Database backup and retention.

### Not Yet Implemented

* Native PDF export.
* Native Excel export.
* Deadline reminder automation.
* Email notifications.
* Mobile application.

---

## Future Enhancements

The following items are optional extensions and are **not part of the original seven-phase roadmap**:

* Email notifications.
* Native PDF export.
* Mobile app.
* Deadline reminders.

These should be treated as future extensions rather than incomplete phases of the current implementation.

---

## Operational Notes

### Installer

Use:

```text
http://localhost/pms/install.php
```

only during initial setup.

After installation, delete or rename:

```text
install.php
```

when appropriate.

### Login

Use:

```text
http://localhost/pms/modules/auth/login.php
```

### Local Database Defaults

```env
DB_HOST=localhost
DB_NAME=pms_db
DB_USER=root
DB_PASS=
```

These values represent the default XAMPP local configuration and should be changed for real hosting environments.

### Database Migration Rule

Never re-run:

```text
database/schema.sql
```

against an already initialized installation unless a full database recreation is intentionally required.

For upgrades, apply only the migrations that have not yet been executed.

### Backup Storage

Keep generated backups under:

```text
storage/backups/
```

and ensure that this directory remains excluded from version control.

---

## License

License information is not currently specified.
