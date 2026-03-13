````markdown
# DATABASE_SCHEMA.md

# Oxford Houses Central Management System  
## Database Schema Documentation

This document describes the primary database structure used by the Oxford Houses Central Management System.

The platform is built around a **central master database** that manages users, houses, access control, contracts, logs, and related system records while keeping each house logically separated.

---

# Schema Overview

The database is organized around these core areas:

- **User authentication and role management**
- **House registry**
- **House-to-user access mapping**
- **Audit logging**
- **Contract tracking**
- **Optional house-specific operational modules**

---

# Core Tables

## 1. `oxford_master_users`

Stores all system users.

### Purpose
This table contains login accounts for all platform users, including house-level users and system-wide administrators.

### Main Fields

| Column | Type | Description |
|---|---|---|
| `id` | INT UNSIGNED PK AI | Unique user ID |
| `full_name` | VARCHAR(255) | Full display name |
| `email` | VARCHAR(255) UNIQUE | Login email address |
| `password_hash` | VARCHAR(255) | Hashed password created with `password_hash()` |
| `role` | VARCHAR(50) | User role |
| `status` | VARCHAR(50) | Account status such as `active` or `inactive` |
| `created_at` | DATETIME / TIMESTAMP | Date the user was created |
| `updated_at` | DATETIME / TIMESTAMP | Last update timestamp |

### Notes
- Email should be unique.
- Passwords are never stored in plain text.
- Roles commonly include:
  - `house_user`
  - `house_manager`
  - `central_admin`
  - `super_admin`

---

## 2. `oxford_master_houses`

Stores all registered houses.

### Purpose
This table defines every Oxford House managed by the system.

### Main Fields

| Column | Type | Description |
|---|---|---|
| `id` | INT UNSIGNED PK AI | Unique house ID |
| `house_name` | VARCHAR(255) | House name |
| `house_code` | VARCHAR(100) | Optional unique short code |
| `city` | VARCHAR(150) | House city |
| `state` | VARCHAR(100) | House state |
| `is_active` | TINYINT(1) | Whether the house is active |
| `created_at` | DATETIME / TIMESTAMP | Date the house was created |
| `updated_at` | DATETIME / TIMESTAMP | Last update timestamp |

### Notes
- `is_active = 1` means available for assignment and use.
- One house can have many users through the access mapping table.

---

## 3. `oxford_master_house_user_access`

Maps users to houses.

### Purpose
This table controls which users can access which houses.

### Main Fields

| Column | Type | Description |
|---|---|---|
| `id` | INT UNSIGNED PK AI | Unique mapping ID |
| `house_id` | INT UNSIGNED FK | Linked house ID |
| `user_id` | INT UNSIGNED FK | Linked user ID |
| `is_primary` | TINYINT(1) | Marks the user’s primary house |
| `created_at` | DATETIME / TIMESTAMP | Mapping creation date |

### Relationships
- `house_id` → `oxford_master_houses.id`
- `user_id` → `oxford_master_users.id`

### Notes
- A user may be linked to one or many houses.
- A house may contain one or many users.
- Usually the first assigned house is marked as primary.

---

## 4. `oxford_audit_logs`

Stores audit and activity records.

### Purpose
Tracks important system actions for security, traceability, and oversight.

### Main Fields

| Column | Type | Description |
|---|---|---|
| `id` | BIGINT / INT PK AI | Unique audit ID |
| `house_id` | INT UNSIGNED NULL | Related house if applicable |
| `user_id` | INT UNSIGNED NULL | User who performed the action |
| `action_name` | VARCHAR(100) | Internal action identifier |
| `page_name` | VARCHAR(255) | Source page or module |
| `target_table` | VARCHAR(255) | Table affected |
| `target_id` | VARCHAR(255) | Record ID affected |
| `details` | TEXT / JSON | Extra action details |
| `created_at` | DATETIME / TIMESTAMP | Action timestamp |

### Common Logged Events
- `user_created`
- `user_access_updated`
- `contract_uploaded`
- `contract_fulfilled`
- `contract_voided`
- `password_changed`
- `contract_password_changed`

### Notes
- `details` may be stored as JSON or plain serialized metadata depending on implementation.
- Logs are meant to be human-readable in the UI.

---

# Contract Tables

## 5. `contracts` or house-specific contract table

The exact name may vary by implementation. In many setups the contract module may use a table dedicated to storing uploaded contracts and their status.

### Purpose
Tracks contract records, scanned copies, and stamping actions.

### Typical Fields

| Column | Type | Description |
|---|---|---|
| `id` | INT UNSIGNED PK AI | Contract ID |
| `house_id` | INT UNSIGNED FK | Related house |
| `member_name` | VARCHAR(255) | Member or resident name |
| `contract_date` | DATE | Contract date |
| `file_path` | VARCHAR(500) | Uploaded scanned file path |
| `status` | VARCHAR(50) | Contract status |
| `contract_stamp` | VARCHAR(50) NULL | Fulfilled / Voided marker |
| `stamped_by_user_id` | INT UNSIGNED NULL | User who stamped the contract |
| `stamped_at` | DATETIME / TIMESTAMP NULL | Stamp timestamp |
| `created_at` | DATETIME / TIMESTAMP | Record creation date |
| `updated_at` | DATETIME / TIMESTAMP | Last update date |

### Typical Status Values
- `active`
- `fulfilled`
- `voided`

### Notes
- Contract stamping is password protected.
- Stamping privileges are restricted by role.

---

# Security / Settings Storage

## 6. System settings table (implementation may vary)

Some installations store configurable values such as the contract stamp password in a dedicated settings table.

Possible names include:
- `oxford_settings`
- `oxford_master_settings`
- module-specific settings tables

### Purpose
Stores internal system configuration values in the database.

### Typical Fields

| Column | Type | Description |
|---|---|---|
| `id` | INT PK AI | Setting record ID |
| `setting_key` | VARCHAR(150) UNIQUE | Unique setting name |
| `setting_value` | TEXT | Setting value |
| `updated_at` | DATETIME / TIMESTAMP | Last update date |
| `updated_by_user_id` | INT UNSIGNED NULL | User who last changed the setting |

### Common Stored Values
- Contract stamp password hash
- Module preferences
- House defaults

### Notes
- Sensitive settings should be hashed when appropriate.
- Do not store security passwords as plain text.

---

# Optional House-Level Operational Tables

Depending on which modules are enabled, the system may also include house-specific tables for forms, finance records, reports, and checklists.

Examples include:

- `member_financial_contracts`
- `bedroom_essentials_checklists`
- `house_ledgers`
- `meeting_reports`
- `incident_reports`
- `inventory_logs`

These modules typically include a `house_id` field so records remain logically separated by house.

---

# Relationship Diagram

## High-Level Relationship Map

```text
oxford_master_users
    |
    | 1-to-many
    |
    +----< oxford_master_house_user_access >----+ 
                                                |
                                                | many-to-1
                                                |
                                        oxford_master_houses

oxford_master_users
    |
    | 1-to-many
    |
    +----< oxford_audit_logs

oxford_master_houses
    |
    | 1-to-many
    |
    +----< oxford_audit_logs

oxford_master_houses
    |
    | 1-to-many
    |
    +----< contracts

oxford_master_users
    |
    | 1-to-many
    |
    +----< contracts (via stamped_by_user_id, optional)
````

---

# Role Model Reference

## User Roles

| Role            | Description                            |
| --------------- | -------------------------------------- |
| `house_user`    | Standard house-level user              |
| `house_manager` | Core member / house manager            |
| `central_admin` | Central system administrator           |
| `super_admin`   | Full unrestricted system administrator |

---

# Access Logic

## House Access Model

A user does not automatically have access to every house.

Access is granted through:

* `oxford_master_house_user_access`

This allows:

* one user to access multiple houses
* one house to have multiple assigned users
* one house to be marked as a primary house for a user

---

# Audit Logging Model

Every major administrative action should call the audit logger.

## Recommended Logged Metadata

* acting user ID
* related house ID
* action name
* page name
* target table
* target record ID
* readable details payload

## Example

```json
{
  "email": "admin@example.com",
  "role": "central_admin",
  "house_ids": [2, 5, 7]
}
```

---

# Recommended Indexes

To improve performance, these indexes are recommended.

## `oxford_master_users`

* UNIQUE index on `email`
* index on `role`
* index on `status`

## `oxford_master_houses`

* index on `house_name`
* index on `is_active`

## `oxford_master_house_user_access`

* index on `house_id`
* index on `user_id`
* composite unique index on (`house_id`, `user_id`)

## `oxford_audit_logs`

* index on `user_id`
* index on `house_id`
* index on `action_name`
* index on `created_at`

## `contracts`

* index on `house_id`
* index on `member_name`
* index on `status`
* index on `contract_date`

---

# Recommended Foreign Keys

Where supported and desired, these foreign keys are recommended:

```text
oxford_master_house_user_access.house_id
    -> oxford_master_houses.id

oxford_master_house_user_access.user_id
    -> oxford_master_users.id

oxford_audit_logs.house_id
    -> oxford_master_houses.id

oxford_audit_logs.user_id
    -> oxford_master_users.id

contracts.house_id
    -> oxford_master_houses.id

contracts.stamped_by_user_id
    -> oxford_master_users.id
```

---

# Data Separation Strategy

The system keeps each house’s records isolated through a combination of:

* house-based record ownership
* access mapping table
* role-based permissions
* central admin oversight
* filtered queries by `house_id`

This means users only interact with the houses they are assigned to, unless their role grants wider access.

---

# Security Notes

* Passwords must always be stored as hashes.
* Contract stamp passwords should also be hashed when stored.
* Role checks should be enforced server-side on every protected page.
* Audit logs should never be editable by standard users.
* Sensitive actions should always generate audit log entries.

---

# Suggested Future Enhancements

Recommended database improvements for future versions:

* dedicated `oxford_master_roles` table
* dedicated `oxford_permissions` table
* soft delete support on users and houses
* login history table
* password reset token table
* contract status history table
* per-house settings table
* attachment metadata table

---

# Summary

The Oxford Houses Central Management System database is designed to support:

* secure multi-house administration
* role-based access control
* flexible user-to-house assignments
* contract management
* human-readable audit logging
* modular house-specific expansion

This schema provides a strong foundation for growth while keeping each house’s data organized and separated.

```
```
