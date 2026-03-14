````markdown
# Oxford Houses Central Management System

A centralized multi-house management platform for Oxford Houses that keeps each house’s data separated while providing secure system-wide administration, reporting, contract management, and user access control.

## Overview

The Oxford Houses Central Management System is designed to allow multiple houses to operate under one platform without mixing their records. Central administrators can manage users, permissions, and houses across the system, while house-level users only see the data they are authorized to access.

The system includes role-based permissions, contract tools, readable audit logs, security controls, and support for both MySQL and SQLite installations.

## Core Features

### Multi-House Support
- Manage multiple Oxford Houses from one system
- Keep house data isolated and organized
- Assign users to one or more houses
- Support central oversight without mixing house records

### Role-Based Access Control
Supported roles include:
- **House User**
- **Core Member / House Manager**
- **Central Admin**
- **Super Admin**

Each role is enforced throughout the system to protect sensitive data and administrative actions.

### User Management
- Create and manage users
- Assign roles
- Activate or deactivate accounts
- Assign access to one or more houses
- Allow users to change their own password

### Contract Management
- Store and manage contracts
- Upload scanned contract copies
- Protect contract actions with a contract password
- Allow authorized roles to change the contract password
- Support contract status workflows such as fulfilled or voided

### Security
- Password hashing with PHP
- Protected contract password management
- Session-based authentication
- Role-restricted actions
- Secure audit trail support

### Audit Logging
- Readable system activity logs
- Track user creation, updates, and permission changes
- Track contract-related changes
- Track administrative actions

### Installer Support
- Full install script included
- Detects available database drivers
- Supports **MySQL / MariaDB**
- Supports **SQLite**
- Creates the initial database structure
- Seeds the first super admin account
- Writes install lock protection

## Technology Stack

### Backend
- PHP 8+

### Database
- MySQL / MariaDB
- SQLite

### Frontend
- HTML
- CSS
- Responsive card-based admin interface

## Project Structure

```text
extras/
├── data/
├── footer.php
├── header.php
├── install.lock
├── master_config.php
└── sql/

president/
└── contracts.php

install.php
central_admin.php
users_admin.php
security.php
login.php
```

## Installation

### 1. Upload the Files

Upload the project to your server directory.

Example:

```text
/var/www/html/oxford/
```

or

```text
C:\xampp\htdocs\oxford\
```

### 2. Run the Installer

Open the installer in your browser:

```text
http://yourdomain.com/oxford/install.php
```

### 3. Choose a Database

The installer can detect and use:

* MySQL / MariaDB
* SQLite

### 4. Complete Setup

During installation, you can:

* set the system name
* configure the database
* create the first super admin
* set the initial contract password
* optionally seed a demo house

### 5. Remove or Rename the Installer

After installation, delete or rename `install.php` for security.

## Permissions Overview

| Role          | Access Level                |
| ------------- | --------------------------- |
| House User    | Basic assigned house access |
| House Manager | House-level management      |
| Central Admin | Multi-house administration  |
| Super Admin   | Full system access          |

## Security Notes

* Passwords are stored as hashes
* Contract password changes are restricted by role
* Sensitive actions should be logged
* Install lock prevents accidental reinstallation

## Documentation Included

The project can include:

* `README.md`
* `INSTALL.md`
* `CHANGELOG.md`
* `ADMIN_GUIDE.md`
* `DEVELOPER.md`
* `RELEASE_NOTES.md`
* `DATABASE_SCHEMA.md`

## Intended Use

This system is designed for Oxford House operational management where multiple houses need centralized oversight with controlled access and separated records.

## Initial Commit Summary

* add multi-house central management structure
* add role-based access control
* add user and house access management
* add contract password protection
* add audit logging support
* add installer with database detection
* add project documentation

## License

Intended for Oxford House operational and administrative use.

