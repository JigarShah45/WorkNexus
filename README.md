# WorkNexus

A full-featured employee management system built with CodeIgniter 3. WorkNexus gives HR teams and administrators one place to manage employees, attendance, leave, client meetings, salary hikes and reports — with Google Calendar/Meet integration for client meetings and role-based access for Admin, HR, Manager and Employee users.

> **Security notice:** this repository contains **no real credentials**. Every secret (database password, SMTP password, encryption key, Google OAuth client secret and refresh token) lives outside git — in the local `.env` file and `application/config/google/*.json`, both gitignored. See [Security & Credentials](#security--credentials).

## Features

- **Employee Management** — Create, view, edit, and soft-delete employee profiles with department assignment
- **Department Management** — CRUD operations for organizational departments
- **Attendance Tracking** — Clock in/out with configurable shift rules, grace periods, half-day logic, automatic closure and overtime calculation
- **Leave Management** — Request, approve, reject leave with multiple leave types (Sick, Casual, Personal, etc.)
- **Client Meetings** — Schedule meetings with Google Calendar & Google Meet integration, file attachments for minutes/presentations
- **Salary Hike Management** — Propose, approve, or reject salary hikes with attendance-based scoring
- **Notifications** — Real-time notification system for leave and meeting updates
- **Reports** — Attendance reports, leave summaries, and employee analytics (DataTables export)
- **Audit Trail** — Track all data modifications with before/after values
- **User Logs** — Login/logout tracking with IP and user-agent logging
- **Role-Based Access Control (RBAC)** — 16 granular permissions across Admin, HR, Manager and Employee roles
- **Profile Management** — User profile pictures and personal information
- **Dark Theme** — Full dark mode support across all pages

## What has been done

The application is feature-complete for its first release:

| Area | Status |
|------|--------|
| Core modules | 20 controllers, 15 models, 17 view directories — employees, departments, users, attendance, leave, meetings, hikes, reports, notifications, profile, settings |
| Authentication & RBAC | Login/logout, session hardening, permission library with 16 permissions × 4 roles enforced in `MY_Controller` |
| Attendance engine | Configurable business rules in `application/config/attendance.php`: 11:00–19:00 shift (IST), 15-minute grace, half-day after 11:15, valid clock-out from 18:30, auto-closure at 19:00, derived hours-worked and overtime |
| Leave workflow | Request → approve/reject → notify, multiple leave types, balance tracking |
| Client meetings | Google Calendar + Google Meet creation via OAuth, attachments, status tracking |
| Salary hikes | Propose/approve/reject flow with attendance-based scoring and history |
| Email | Centralized `Worknexusmailer` library — multipart HTML + plain text, reply-to matching, duplicate-send protection, SMTP with PHP `mail()` fallback |
| Data integrity | Audit trail (before/after values), user logs (IP + user-agent), soft deletes, CSRF protection on all forms |
| Frontend | Modular CSS architecture (19 files: variables/theme/common/utilities + per-module styles), shared DataTable initialisers, standardised forms/buttons, responsive layouts, full dark theme |
| Configuration | Database, base URL, encryption key and SMTP read from environment variables via a small `.env` loader in `index.php` |
| Database | 16 SQL files: schema, seeds (permissions/roles/shifts), demo data and incremental migrations |
| Documentation | This README, `.env.example` template, `tawkto_knowledge_base.txt` support guide |

## What needs to be done

- [ ] **Verify & rotate Google OAuth credentials** — run the audit in `update_gitignore.ps1` (step 4) to check whether `credentials.json`/`token.json` ever entered git history. If they did, they are exposed: create a new client secret in Google Cloud Console, revoke the old refresh token (see [Security & Credentials](#security--credentials)), then purge the files from history (`git filter-repo --path application/config/google/credentials.json --path application/config/google/token.json --invert-paths` + force push)
- [ ] **Automated tests + CI** — no test suite yet; add PHPUnit coverage for the attendance, leave and hike calculations and wire up GitHub Actions
- [ ] **Production environment defaults** — `ENVIRONMENT` and `base_url` should default to `production`/real domain in deployment (both are already overridable via `CI_ENV` / `WN_BASE_URL`)
- [ ] **HTTPS hardening** — set `cookie_secure = TRUE` and force HTTPS in production (`application/config/config.php`)
- [ ] **Remove dead code** — `application/controllers/Hikes_fixed.php` (test stub) and `Email_Test.php` are no longer needed
- [ ] **Password reset & account lockout** — self-service reset via email and lockout after repeated failed logins
- [ ] **Move employee photos out of the webroot** — serve profile images through a controller with access checks instead of a public folder
- [ ] **Scheduled jobs** — document/automate attendance auto-closure (currently handled by the `Cron` controller; needs a real cron entry)
- [ ] **Deployment guide** — tested Nginx/Apache configuration, writable-directory permissions, `CI_ENV=production` checklist

## Tech Stack

- **Backend**: PHP (CodeIgniter 3)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3 (custom modular architecture with CSS variables), JavaScript (jQuery, DataTables)
- **APIs**: Google Calendar API & Google Meet (via `google/apiclient`)
- **Email**: SMTP (configurable via environment variables)
- **Build Tools**: Composer

## Requirements

- PHP >= 5.6 (7.4+ recommended)
- MySQL >= 5.7 or MariaDB >= 10.2
- Composer
- Apache/Nginx web server with mod_rewrite
- PHP extensions: `mysqli`, `openssl`, `curl`, `json`, `mbstring`, `gd`

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/JigarShah45/WorkNexus.git
cd WorkNexus
```

### 2. Install Composer Dependencies

```bash
composer install
```

### 3. Create the Database

```sql
CREATE DATABASE employee CHARACTER SET utf8 COLLATE utf8_general_ci;
```

### 4. Import the Schema

```bash
mysql -u root -p employee < database/schema.sql
```

### 5. Import Seed Data (Permissions, Roles, Shifts)

```bash
mysql -u root -p employee < database/seed.sql
```

### 6. Import Migration Data (Departments, Employees, Users)

```bash
mysql -u root -p employee < database/migrate_data.sql
```

### 7. Apply Additional Migrations (in order)

```bash
mysql -u root -p employee < database/schema_updates.sql
mysql -u root -p employee < database/add_google_meet_columns.sql
mysql -u root -p employee < database/add_email_sent_columns.sql
mysql -u root -p employee < database/attendance_business_rules.sql
mysql -u root -p employee < database/fix_leave_schema.sql
mysql -u root -p employee < database/fix_permissions.sql
mysql -u root -p employee < database/fix_attendance_duplicates.sql
mysql -u root -p employee < database/fix_hr_salary_permissions.sql
mysql -u root -p employee < database/fix_salary_table_structure.sql
mysql -u root -p employee < database/enable_employee_meetings.sql
mysql -u root -p employee < database/salary_compensation_update.sql
mysql -u root -p employee < database/notifications.sql
mysql -u root -p employee < database/migrate_employee_dashboard.sql
```

### 8. Configure Environment Variables

Copy the example file and set your values:

```bash
cp .env.example .env
```

Edit `.env` with your real database credentials, encryption key and SMTP settings. A small loader in `index.php` reads this file before CodeIgniter boots, so the values are available to `getenv()` throughout the app. Alternatively you can set them as real system environment variables or in your web server config (Apache `SetEnv`, Nginx `fastcgi_param`) — real environment values always win over the file.

**The `.env` file is gitignored. Never commit it.**

### 9. Configure Writable Directories

Ensure the following directories are writable by the web server:

```
application/cache/
application/logs/
application/uploads/
```

### 10. Configure Google OAuth (Optional)

If you want Google Calendar/Meet integration:

1. Create a project in the [Google Cloud Console](https://console.cloud.google.com/)
2. Enable the Google Calendar API
3. Create OAuth 2.0 credentials (Web application type)
4. Set the authorized redirect URI to: `http://localhost:7328/employee_management/google/callback`
5. Download the credentials JSON and save it as `application/config/google/credentials.json`
6. The `token.json` file will be generated automatically on first OAuth authentication

**Important:** `credentials.json` and `token.json` are gitignored. Each developer sets up their own — they are never committed.

### 11. Start the Application

Access the application in your browser:

```
http://localhost:7328/employee_management/
```

## Configuration

### Database

| Variable | Description | Default |
|----------|-------------|---------|
| `WN_DB_HOST` | Database hostname | `localhost` |
| `WN_DB_USERNAME` | Database username | `root` |
| `WN_DB_PASSWORD` | Database password | *(empty)* |
| `WN_DB_DATABASE` | Database name | `employee` |

### Application

| Variable | Description | Default |
|----------|-------------|---------|
| `CI_ENV` | `development` / `testing` / `production` | `development` |
| `WN_BASE_URL` | Base URL of the install | `http://localhost:7328/employee_management/` |
| `WN_ENCRYPTION_KEY` | 32+ character random string for sessions/encryption | *(empty — must be set for encryption)* |

Generate a key: `php -r "echo bin2hex(random_bytes(16));"`

### SMTP Email

| Variable | Description | Default |
|----------|-------------|---------|
| `WN_SMTP_HOST` | SMTP server host | `smtp.gmail.com` |
| `WN_SMTP_PORT` | SMTP server port | `587` |
| `WN_SMTP_USER` | SMTP username | — |
| `WN_SMTP_PASS` | SMTP password | — |
| `WN_FROM_EMAIL` | Sender email address (must match the authenticated account) | — |

### Google Calendar

Place OAuth credentials at `application/config/google/credentials.json`. The calendar ID is configured in `application/config/google_calendar.php` (default: `primary`).

## Database Setup

The `database/` directory contains SQL files in the following categories:

| File | Purpose |
|------|---------|
| `schema.sql` | Complete database schema (13 tables) |
| `seed.sql` | Default permissions (16), role mappings, shifts |
| `schema_updates.sql` | RBAC, audit trail, meetings, attendance, leave, salary |
| `migrate_data.sql` | Demo data: 12 departments, 27 employees, 2 users |
| `add_*.sql` | Schema migration patches |
| `fix_*.sql` | Bug fix migrations |
| `enable_*.sql` | Feature enablement migrations |
| `salary_*.sql` | Salary workflow updates |
| `notifications.sql` | Notifications table |

**First admin account:** create it locally after importing the seeds — for example by inserting a row into `tbl_users` with a hash generated via `php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT);"`. Any helper script used for this must stay local and gitignored (see `.gitignore`). **Change every default password immediately.**

## Google Integration

1. Follow the Google OAuth setup in Installation step 10
2. Place `credentials.json` in `application/config/google/`
3. On first meeting creation, you'll be redirected to Google for OAuth consent
4. The `token.json` file is created automatically and stored locally
5. Calendar events are created using the primary calendar of the connected Google account

## Roles and Permissions

| Role | Dashboard | Employees | Departments | Users | Reports | Settings | Attendance | Leave | Meetings | Hikes | Audit | User Logs | View Data | Edit Data | View Salary | Edit Salary |
|------|-----------|-----------|-------------|-------|---------|----------|------------|-------|----------|-------|-------|-----------|-----------|-----------|-------------|-------------|
| Admin | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes |
| HR | Yes | Yes | Yes | Yes | Yes | No | Yes | Yes | Yes | Yes | No | No | Yes | Yes | Yes | No |
| Manager | Yes | Yes | No | No | Yes | No | Yes | Yes | Yes | No | No | No | Yes | No | No | No |
| Employee | Yes | No | No | No | No | No | Yes | Yes | Yes | Yes* | No | No | Yes | No | No | No |

\* Employee role has limited salary visibility (own salary page only).

## Project Structure

```
WorkNexus/
├── application/              # CodeIgniter application code
│   ├── config/               # Configuration files (reads env vars)
│   │   ├── google/           # Google OAuth credentials (gitignored)
│   │   ├── database.php      # Database configuration (WN_DB_* env vars)
│   │   ├── config.php        # Base URL, encryption key (env vars)
│   │   ├── email.php         # SMTP configuration (WN_SMTP_* env vars)
│   │   ├── attendance.php    # Attendance business rules
│   │   └── google_calendar.php
│   ├── controllers/          # 20 controllers
│   ├── models/               # 15 models
│   ├── views/                # 17 view directories
│   ├── core/MY_Controller.php# Auth + permission gate
│   ├── libraries/            # Permissions, Google, Mailer
│   ├── cache/                # Runtime cache (gitignored)
│   ├── logs/                 # Application logs (gitignored)
│   └── uploads/              # User uploads (gitignored)
├── assets/                   # Frontend assets
│   ├── css/                  # Modular CSS (19 files)
│   ├── js/                   # JavaScript modules (4 files)
│   └── images/               # Static images (logo)
├── database/                 # SQL schema, migrations, seed data
├── system/                   # CodeIgniter 3 framework core
├── vendor/                   # Composer dependencies (gitignored)
├── .env                      # Local secrets (gitignored)
├── .env.example              # Environment configuration template
├── .gitignore                # Git ignore rules
├── composer.json             # Composer dependencies
├── composer.lock             # Composer lock file
├── index.php                 # Entry point + .env loader
└── README.md                 # This file
```

Gitignored (kept out of the repository on purpose): `.env`, Google OAuth files, `employee_images/`, debug/diagnostic scripts, application logs, cache, uploads, `vendor/`, internal notes.

## Security & Credentials

**Where secrets live**

| Secret | Location | In git? |
|--------|----------|---------|
| Database password | `.env` (`WN_DB_PASSWORD`) | No — gitignored |
| Encryption key | `.env` (`WN_ENCRYPTION_KEY`) | No — gitignored |
| SMTP password | `.env` (`WN_SMTP_PASS`) | No — gitignored |
| Google OAuth client secret | `application/config/google/credentials.json` | No — gitignored |
| Google OAuth refresh token | `application/config/google/token.json` | No — gitignored |

**Rules**

- **Never commit `.env`** or any file containing real credentials
- **Never commit** `application/config/google/credentials.json` or `token.json`
- **Never commit** application logs, cache, uploads, or user-uploaded private files
- **Never commit** database dumps containing real user data
- `.env.example` is the only environment file allowed in git — it contains placeholders only
- Set `CI_ENV=production` in production; use HTTPS; keep `cache/`, `logs/`, `uploads/` non-public

**If a credential was ever pushed to GitHub, treat it as compromised:**

1. **Google OAuth** — Google Cloud Console → Credentials → generate a **new client secret** for the OAuth client, replace `credentials.json` locally. Revoke the leaked refresh token (Google Account → Security → Third-party access, or `POST https://oauth2.googleapis.com/revoke`).
2. **SMTP app password** — revoke and generate a new one in Google Account → App passwords.
3. **Database/encryption key** — change the value and regenerate `WN_ENCRYPTION_KEY`.
4. **Purge from history** — `git filter-repo` on the affected paths, then `git push --force-with-lease`.
5. Anyone who cloned the repo while the secret was public must be assumed to have a copy.

## Development

1. `cp .env.example .env` and fill in local values
2. Set `CI_ENV=development` for full error reporting, `production` for deployments
3. Run `composer install` after cloning
4. Import the database schema and seed data
5. Configure your web server to point to the project root
6. Access at `http://localhost:7328/employee_management/`

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Blank page after install | Run `composer install` and check PHP error log |
| Database connection error | Verify `WN_DB_*` values in `.env` (or your server env vars) |
| Settings ignored | Confirm `.env` is in the project root; real environment values override it |
| CSRF errors | Clear browser cookies; check `application/config/config.php` CSRF settings |
| Google Calendar 404 | Ensure OAuth token is valid and calendar ID is `primary` |
| SMTP not sending | Set `WN_SMTP_*` in `.env`; check firewall allows port 587 |
| `max_allowed_packet` error | Increase `max_allowed_packet` in MySQL config (e.g. `64M`) |
| Session not saving | Ensure `application/cache/` is writable |

## License

This project is licensed under the MIT License. See [license.txt](license.txt) for details.
