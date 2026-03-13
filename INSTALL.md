# Installation Guide

## Requirements

Server Requirements:

- PHP 8+
- MySQL or MariaDB
- Apache / Nginx
- PDO enabled

Optional:

- SQLite support

---

## Step 1 — Upload Files

Upload the system files to your web server.

Example:

/var/www/oxford/

or

xampp/htdocs/oxford/

---

## Step 2 — Create Database

Create a database such as:

secretary

Import the SQL installer located in:

extras/sql/

---

## Step 3 — Configure Database

Edit:

extras/master_config.php

Update database credentials:

```php
$dbHost = 'localhost';
$dbName = 'secretary';
$dbUser = 'secretary';
$dbPass = 'yourpassword';
```

## Step 4 — Login

Access the system through your browser:

http://yourdomain/oxford/

Login using the administrator account created during setup.

## Optional SQLite Setup

If using SQLite:

The database file will be stored in:

extras/data/oxford.db


---

# CHANGELOG.md
```markdown
# Changelog

## Security & Audit Update

### Added

- User password change functionality
- Contract password management
- Security settings panel
- Improved readable activity logs

### Improved

- Audit log readability
- Central admin activity tracking
- User access management tools

### Security

- Contract stamp password protection
- Role-restricted password changes
- Secure password hashing

### Internal Changes

Added:
- security.php

Updated:
- master_config.php
- header.php
- contracts.php
- users_admin.php

SQL installer updated.