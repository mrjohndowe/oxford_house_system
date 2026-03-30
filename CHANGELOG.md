# Changelog

All notable changes to the Oxford House Central System will be documented in this file.

---

## [4.3.0] - 2026-03-30

### 🚀 Major Features

#### Multi-Device Responsive UI
- Added full responsive support for:
  - Mobile devices
  - Tablets
  - Desktop screens
- Introduced `/assets/css/responsive.css`
- Tables and layouts now scale properly across all screen sizes

---

#### House-Level Security System (Isolation Layer)
- Implemented strict house-based access control
- Added `/core/house_guard.php`
- Users can only:
  - View
  - Edit
  - Submit
  data belonging to their assigned house

---

#### Shared Chapter & State Access
- Introduced global read access for:
  - `/chapter/`
  - `/state/`
- All houses can view shared governance files
- Maintains Oxford House structure compliance

---

#### Read-Only Protection
- Files become read-only when accessed outside authorized house
- Unauthorized access redirects to:
  - `access_denied.php`

---

#### Automatic Starting Balance System
- Added `/core/balance_helper.php`
- New forms now:
  - Pull previous ending balance
  - Auto-fill starting balance
- Applies to:
  - Financial reports
  - Ledgers
  - Comptroller forms

---

#### Simple Install Script
- Added `/install.php`
- Automatically:
  - Creates central database
  - Imports schema
  - Prepares system for multi-house use

---

### 🔒 Security Improvements
- Session-based house validation enforced globally
- Prevents cross-house data leakage
- Prepares system for role-based permissions

---

### 🧱 Structural Changes
- Added new directories:
  - `/core/`
  - `/assets/css/`
- Modularized access control + shared logic

---

### ⚠️ Developer Notes
- All house-specific tables must include:
  - `house_id` column
- Login system must set:
  - `$_SESSION['house_id']`

---

## [4.2.0] - Previous Release
- Discord voting integration
- Motion tracking system
- Quorum enforcement
- Role-based vote counting
- JSON + MySQL sync support

---

## [4.1.0]
- Financial forms auto-save
- MySQL persistence
- Print layout optimization
- Signature support

---

## [4.0.0]
- Initial central system release
- Multi-form PHP architecture
- Oxford House operational tools

---
