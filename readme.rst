# WorkNexus

A full-featured employee management system built with CodeIgniter 3. WorkNexus provides HR teams and administrators with tools to manage employees, attendance, leave, meetings, salary hikes, and more — with Google Calendar/Meet integration for client meetings.

## Features

- **Employee Management** — Create, view, edit, and soft-delete employee profiles with department assignment
- **Department Management** — CRUD operations for organizational departments
- **Attendance Tracking** — Clock in/out with configurable shift rules, grace periods, overtime calculation, and auto-closure
- **Leave Management** — Request, approve, reject leave with multiple leave types (Sick, Casual, Personal, etc.)
- **Client Meetings** — Schedule meetings with Google Calendar & Google Meet integration, file attachments for minutes/presentations
- **Salary Hike Management** — Propose, approve, or reject salary hikes with attendance-based scoring
- **Notifications** — Real-time notification system for leave and meeting updates
- **Reports** — Attendance reports, leave summaries, and employee analytics
- **Audit Trail** — Track all data modifications with before/after values
- **User Logs** — Login/logout tracking with IP and user-agent logging
- **Role-Based Access Control (RBAC)** — Granular permissions for Admin, HR, Manager, and Employee roles
- **Profile Management** — User profile pictures and personal information
- **Dark Theme** — Full dark mode support across all pages

## Tech Stack

- **Backend**: PHP (CodeIgniter 3)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3 (custom modular architecture), JavaScript (jQuery, DataTables)
- **APIs**: Google Calendar API (via `google/apiclient`)
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

Copy the example environment file and set your values:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials and settings. Alternatively, set these as system environment variables or configure them in your web server (Apache `SetEnv` or Nginx `fastcgi_param`).

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

**Important**: Never commit `credentials.json` or `token.json` to version control.

### 11. Set the Base URL

Edit `application/config/config.php` and set:

```php
$config['base_url'] = 'http://your-domain.com/';
```

Or set the `WN_BASE_URL` environment variable.

### 12. Start the Application

Access the application in your browser:

```
http://localhost:7328/employee_management/
```

## Configuration

### Database

Configured via environment variables:

| Variable | Description | Default |
|----------|-------------|---------|
| `WN_DB_HOST` | Database hostname | `localhost` |
| `WN_DB_USERNAME` | Database username | — |
| `WN_DB_PASSWORD` | Database password | — |
| `WN_DB_DATABASE` | Database name | — |

### SMTP Email

| Variable | Description | Default |
|----------|-------------|---------|
| `WN_SMTP_HOST` | SMTP server host | `smtp.gmail.com` |
| `WN_SMTP_PORT` | SMTP server port | `587` |
| `WN_SMTP_USER` | SMTP username | — |
| `WN_SMTP_PASS` | SMTP password | — |
| `WN_FROM_EMAIL` | Sender email address | — |

### Encryption Key

| Variable | Description | Default |
|----------|-------------|---------|
| `WN_ENCRYPTION_KEY` | 32+ character random string for sessions/encryption | — |

Generate a key: `php -r "echo bin2hex(random_bytes(16));"`

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

**Default Login Credentials** (from seed data):

| Username | Password | Role |
|----------|----------|------|
| `admin` | Set via `setup_admin.php` or manually hash a password | Admin |

After initial setup, change all default passwords immediately.

## Google Integration

1. Follow the Google OAuth setup in Installation step 10
2. Place `credentials.json` in `application/config/google/`
3. On first meeting creation, you'll be redirected to Google for OAuth consent
4. The `token.json` file is created automatically and stored locally
5. Calendar events are created using the primary calendar of the connected Google account

**Important**: The `credentials.json` and `token.json` files are excluded from version control. Each developer must set up their own Google OAuth credentials.

## Roles and Permissions

| Role | Dashboard | Employees | Departments | Users | Reports | Settings | Attendance | Leave | Meetings | Hikes | Audit | User Logs | View Data | Edit Data | View Salary | Edit Salary |
|------|-----------|-----------|-------------|-------|---------|----------|------------|-------|----------|-------|-------|-----------|-----------|-----------|-------------|-------------|
| Admin | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes | Yes |
| HR | Yes | Yes | Yes | Yes | Yes | No | Yes | Yes | Yes | Yes | No | No | Yes | Yes | Yes | No |
| Manager | Yes | Yes | No | No | Yes | No | Yes | Yes | Yes | No | No | No | Yes | No | No | No |
| Employee | Yes | No | No | No | No | No | Yes | Yes | Yes | Yes* | No | No | Yes | No | No | No |

*Employee role has limited salary visibility (own salary page only).

## Project Structure

```
WorkNexus/
├── application/              # CodeIgniter application code
│   ├── config/               # Configuration files
│   │   ├── google/           # Google OAuth credentials (gitignored)
│   │   ├── database.php      # Database configuration (env vars)
│   │   ├── config.php        # Main application config
│   │   ├── email.php         # SMTP configuration (env vars)
│   │   ├── attendance.php    # Attendance business rules
│   │   └── google_calendar.php
│   ├── controllers/          # 20 controllers
│   ├── models/               # 15 models
│   ├── views/                # 19 view directories
│   ├── libraries/            # Custom libraries (Permissions, Google, Mailer)
│   ├── cache/                # Runtime cache (gitignored)
│   ├── logs/                 # Application logs (gitignored)
│   └── uploads/              # User uploads (gitignored)
├── assets/                   # Frontend assets
│   ├── css/                  # Modular CSS (19 files)
│   ├── js/                   # JavaScript modules (4 files)
│   └── images/               # Static images (logo)
├── database/                 # SQL schema, migrations, seed data
├── employee_images/          # Employee profile images (source for import)
├── system/                   # CodeIgniter 3 framework core
├── vendor/                   # Composer dependencies (gitignored)
├── .env.example              # Environment configuration template
├── .gitignore                # Git ignore rules
├── composer.json             # Composer dependencies
├── composer.lock             # Composer lock file
├── index.php                 # Application entry point
└── README.md                 # This file
```

## Security

- **Never commit `.env`** or any file containing real credentials
- **Never commit** `application/config/google/credentials.json` or `token.json`
- **Never commit** application logs or cache
- **Never commit** user-uploaded private files
- **Never commit** database dumps containing real user data
- **Rotate compromised credentials immediately** — if secrets were ever pushed to a public repository, assume they are compromised
- Set `CI_ENV=production` in production environments
- Use HTTPS in production
- Ensure writable directories (`cache/`, `logs/`, `uploads/`) are not publicly accessible

## Development

1. Set `CI_ENV=development` for full error reporting
2. Set `CI_ENV=production` for production deployments
3. Run `composer install` after cloning
4. Import the database schema and seed data
5. Configure your web server to point to the project root
6. Access at `http://localhost:7328/employee_management/`

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Blank page after install | Run `composer install` and check PHP error log |
| Database connection error | Verify `WN_DB_*` environment variables are set |
| CSRF errors | Clear browser cookies; check `application/config/config.php` CSRF settings |
| Google Calendar 404 | Ensure OAuth token is valid and calendar ID is `primary` |
| SMTP not sending | Set `WN_SMTP_*` environment variables; check firewall allows port 587 |
| `max_allowed_packet` error | Increase `max_allowed_packet` in MySQL config (e.g., `64M`) |
| Session not saving | Ensure `application/cache/` is writable |

## License

This project is licensed under the MIT License. See [license.txt](license.txt) for details.
