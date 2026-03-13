# Oxford Houses Central Management System

A centralized platform designed to manage multiple Oxford Houses while keeping each house’s data isolated. The system provides role-based access, centralized administration, contract management, audit logs, and user management.

---

## Features

### Multi-House Management
- Manage multiple Oxford Houses in one system
- Each house maintains separate data
- Centralized oversight tools

### Role-Based Access Control
Roles supported:

- House User
- Core Member / House Manager
- Central Admin
- Super Admin

Each role has controlled permissions across the platform.

---

### User Management
Admins can:

- Create users
- Assign houses
- Control roles
- Activate / deactivate accounts
- Manage access permissions

Users can also change their own password.

---

### Contract Management
Features include:

- Upload scanned contracts
- Contract fulfillment stamping
- Contract void stamping
- Secure contract password protection

---

### Security
Security protections include:

- Password hashing
- Role-based authorization
- Secure contract stamping
- Session protection
- Audit logging

---

### Audit Logging
The system logs key actions including:

- User creation
- Role changes
- Access updates
- Contract changes
- Administrative actions

Logs are stored in a readable format for auditing.

---

### Technology Stack

Backend
- PHP 8+

Database
- MySQL / MariaDB
- Optional SQLite support

Frontend
- HTML5
- CSS3
- Responsive UI

---

### Project Structure
extras/
├── header.php
├── footer.php
├── master_config.php
└── sql/

central_admin.php
users_admin.php
security.php

president/
└── contracts.php

---

### License
Intended for Oxford House operational use.