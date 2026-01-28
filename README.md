# Pharmacy Manager

A lightweight PHP 8+ + MySQL admin system for pharmacy manager oversight. Built with plain PHP, Bootstrap 5, and vanilla JavaScript. No frameworks.

## Features
- Session-based authentication with admin/staff roles.
- Taxi delivery tracking with event logs and status workflow.
- Multi-pharmacy support with per-location settings.
- Multi-currency support (IQD/USD) with configurable exchange rate.
- Daily cash count entry with denomination totals.
- Staff clock in/out tracking.
- Reports with CSV export.

## Requirements
- IIS + PHP 8+ + MySQL 8+ (or MariaDB 10.4+).
- Database credentials configured in `config/config.php`.

## Quick Start
1. Create a database and import the schema:
   ```sql
   SOURCE schema.sql;
   ```
2. Edit `config/config.php` with your DB credentials.
3. Ensure `session.save_path` is configured in PHP.
4. Launch the site in IIS (point the site root to this repository).
5. Log in with the seeded admin user:
   - **username:** `admin`
   - **password:** `admin123`
6. Add additional pharmacies in the database or via admin tools and switch between them in the top bar.

## Security Notes
- Passwords are hashed with `password_hash()`.
- All database access uses prepared statements.
- CSRF tokens are required for all POST forms.
- Data is never deleted; use status flags.

## Release
See `CHANGELOG.md` and `VERSION` for release information.
