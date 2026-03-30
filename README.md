# Oxford House Central System

A fully integrated multi-house management system designed for Oxford House operations.

Built with:
- PHP (Single-file architecture)
- MySQL (PDO)
- Modular security + multi-house isolation

---

## 🚀 Features

### 🏠 Multi-House Architecture
- Each house operates independently
- Central system manages all houses
- Secure data isolation per house

---

### 🔐 Security Model

#### House-Level Isolation
- Users can only access their own house data
- Enforced via session (`house_id`)
- Prevents cross-house data exposure

#### Shared Governance Files
- Accessible by ALL houses:
  - `/chapter/`
  - `/state/`

#### Read-Only Enforcement
- Unauthorized users cannot edit data
- Redirects to `access_denied.php`

---

### 📱 Responsive UI

Supports:
- Desktop
- Tablet
- Mobile

Automatically adapts layout using: `/assets/css/responsive.css`


---

### 💰 Financial System Enhancements

#### Auto Starting Balances
- New forms automatically inherit:
  - Previous ending balance
- Eliminates manual carry-over errors

---

### ⚙️ Core Modules

Located in `/core/`:

| File | Purpose |
|------|--------|
| `house_guard.php` | Enforces house-level access |
| `shared_access.php` | Allows global chapter/state viewing |
| `balance_helper.php` | Handles financial carry-over logic |

---

### 🛠 Install Instructions

1. Upload files to your server
2. Configure database in: `/extras/master_config.php`
3. Run: `http://yourdomain.com/install.php`
4. Done ✅

---

### 🧠 Required Setup

#### Database Tables
All house-specific tables MUST include: `house_id INT NOT NULL`

---

#### Session Requirement
After login, set:
```php
$_SESSION['house_id'] = $house_id;
```
#### Protect Pages

Add to any protected file:
```php
require_once __DIR__ . '/core/house_guard.php';
enforce_house_access($house_id);
```
#### Enable Responsive UI

Add to header:
```html 
<link rel="stylesheet" href="/assets/css/responsive.css">
```
# 📂 Directory Overview
```
/core/              → Security + logic
/assets/css/        → Responsive styles
/chapter/           → Shared chapter files
/state/             → Shared state files
/president/         → House-specific (restricted)
/comptroller/       → House-specific (restricted)
```
## 🔮 Recommended Next Upgrades
- Role-based permissions (President, Treasurer, etc.)
- Central admin panel (create/manage houses)
- Automatic database cloning per house
- Real-time reporting dashboard
- Permanent scanned document locking

### 📜 License

Internal Oxford House System
Not intended for public redistribution

#### 🤝 Contribution

System designed for structured expansion.
Keep all new modules:


- Single-file compatible
- MySQL integrated
- Layout consistent with printed forms