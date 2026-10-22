# UrbanEats Cloud Kitchen Platform

UrbanEats is a database-driven web application developed using pure HTML5, modern CSS3 (vanilla with CSS variables), PHP 8.x, and MySQL/MariaDB. It serves as an integrated platform for a cloud kitchen operating via delivery and pickup in Sri Lanka.

---

## Team Structure & Components

| Component | Owner | Scope |
|---|---|---|
| **Component A** | Student 1 | **Identity, Access and Administration**: Registration, login, roles, session hardening, profile edit, password reset, user management, audit logging. |
| **Component B** | Student 2 | **Menu, Catalogue and Inventory**: Categories, dishes, images, search, stock control. |
| **Component C** | Student 3 | **Ordering, Payment and Tracking**: Cart, checkout, order workflow, history, sales reports. |

---

## System Requirements & Technology Stack

- **PHP**: 8.0 or higher (Strict types enabled, PDO MySQL extension)
- **Database**: MySQL 5.7+ / MariaDB 10.4+ (managed through phpMyAdmin)
- **Web Server**: Apache (XAMPP / WAMP / LAMP)
- **Frontend**: Semantic HTML5, Vanilla CSS3 (responsive at 360px, 768px, 1200px), Minimal Vanilla JavaScript

---

## Installation & Setup Instructions

### 1. Project Placement
Clone or copy the project into your local server root:
```bash
# For XAMPP on Windows
C:\xampp\htdocs\urbaneats\UrbanEats
```

### 2. Database Import
1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Go to the **Import** tab.
3. Select `database/urbaneats_db.sql` from the project repository and click **Go**.
   *(This automatically creates `urbaneats_db` along with 5 tables and 20+ realistic seed records).*

### 3. Database Configuration
1. In the `config/` directory, copy `config.sample.php` to `config.php`:
   ```bash
   cp config/config.sample.php config/config.php
   ```
2. Verify credentials in `config/config.php`:
   ```php
   return [
       'db_host' => 'localhost',
       'db_name' => 'urbaneats_db',
       'db_user' => 'root',
       'db_pass' => '',
   ];
   ```
   *(Note: `config/config.php` is listed in `.gitignore` and is never committed to GitHub).*

### 4. Running the Application
Access the platform via your browser:
```
http://localhost/urbaneats/UrbanEats/public/index.php
```

---

## Test Accounts

The seed database contains pre-configured test users with role assignments.  
**Password for all test accounts:** `Test@1234`

| Role | Email | Status | Permissions |
|---|---|---|---|
| **Administrator** | `admin@urbaneats.lk` | Active | Full access: User management, role change, activation toggle, audit logs, dashboard. |
| **Kitchen Staff** | `staff1@urbaneats.lk` | Active | Kitchen order processing, menu management, stock adjustments. |
| **Kitchen Staff** | `staff2@urbaneats.lk` | Active | Secondary kitchen staff account. |
| **Customer** | `customer1@urbaneats.lk` | Active | Menu browsing, cart, order placement, order tracking, profile management. |

---

## Features Implemented - Component A

### 1. Authentication & Session Hardening
- **User Registration (`auth/register.php`)**: Full server-side validation, duplicate email check, password hashing (`password_hash()`), automatic assignment of customer role.
- **Secure Login (`auth/login.php`)**: Role-based redirection, session ID regeneration (`session_regenerate_id(true)`), active status verification.
- **Login Throttling**: Temporary lockout for 15 minutes after 5 consecutive failed attempts, tracked via `login_attempts` table.
- **Secure Logout (`auth/logout.php`)**: Clears session array, destroys session cookie, invokes `session_destroy()`.
- **Session Timeout**: Automatic invalidation after 30 minutes (1800s) of inactivity.

### 2. User Profile & Password Recovery
- **Profile Edit (`auth/profile.php`)**: View registered details, update full name and telephone number, role badge.
- **Change Password (`auth/change-password.php`)**: Re-verifies current password before updating to new hashed credentials.
- **Time-Limited Password Reset (`auth/forgot-password.php` & `auth/reset-password.php`)**: Generates cryptographically secure SHA-256 tokens expiring in 1 hour; displays clickable reset link on-screen for simulated recovery.

### 3. Administration & Access Control (`admin/`)
- **Admin Dashboard (`admin/dashboard.php`)**: Real-time summary counts (total users, active/inactive, role breakdown, today's logins) with recent activity feeds.
- **User Management (`admin/users.php`)**:
  - Live search across name, email, and phone.
  - Role filtering (All, Admin, Staff, Customer) and status filtering (Active, Inactive).
  - One-click account activation / deactivation (with self-deactivation protection for the logged-in administrator).
  - Role promotion / demotion dropdown.
- **Audit Logging (`admin/audit-log.php`)**: Reusable `log_action()` recording user ID, action type, IP address, and details. Filterable by action type and searchable with pagination.

### 4. Shared Architecture
- **Reusable Guards (`includes/auth.php`)**: `require_login()` and `require_role(...)` used across all components.
- **CSRF Defense (`includes/csrf.php`)**: Strict token generation and validation required on all POST requests.
- **Error & Exception Handling (`includes/errors.php`)**: Logs uncaught errors/exceptions to `logs/error.log`; displays user-friendly error views without exposing raw SQL or PHP diagnostics.
- **403 Access Denied (`public/access-denied.php`)**: Styled fallback for unauthorized role access.
- **Design System (`public/css/style.css`)**: 100% hand-crafted CSS3 with semantic variables, responsive across 360px, 768px, and 1200px.
