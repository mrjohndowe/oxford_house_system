# Developer Documentation

## System Architecture

The system uses a centralized master database with separate house access mapping.

Core Tables:

- oxford_master_users
- oxford_master_houses
- oxford_master_house_user_access
- oxford_audit_logs

---

## Authentication

Authentication uses:

PHP Sessions
password_hash()
password_verify()

Role validation is handled by:

oxford_require_role()

---

## Logging

System logging uses:

oxford_log_audit()

Logs store:

- user_id
- action_name
- target_table
- target_id
- page_name
- details

---

## Key Files

master_config.php  
Contains:

- database connection
- helper functions
- permission checks

security.php  
Handles password changes.

users_admin.php  
Manages user accounts and house access.

contracts.php  
Handles contract uploads and stamping.

---

## Adding New Modules

To create a new module:

1. Require master_config
2. Validate user role
3. Use oxford_log_audit for actions
4. Follow UI card layout structure