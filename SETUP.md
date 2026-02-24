# SesekaliCBT - Authentication & Role Management Foundation

## Project Overview

Complete foundation for Laravel authentication system with role-based access control (RBAC), including superadmin, admin, and student roles.

## Installation & Setup

### Prerequisites

- PHP 8.2+ (Currently: 8.5.0)
- Composer 2.8+ (Currently: 2.8.12)
- Node.js v25+ (Currently: v25.2.1)
- MySQL Database

### Setup Commands

```bash
# Install dependencies
composer install
npm install

# Run migrations and seed database
php artisan migrate --seed

# Start development server
php artisan serve --host=0.0.0.0
```

## Project Structure

### Database

#### Users Table

- `id` - Primary Key
- `name` - User Name
- `email` - Unique Email Address
- `password` - Hashed Password (bcrypt)
- `role` - Enum: superadmin | admin | student
- `is_active` - Boolean (default: true)
- `timestamps` - created_at, updated_at

#### Seeded Users

```
ID | Name         | Email                 | Role       | Active
1  | Super Admin   | superadmin@localhost  | superadmin | 1
2  | Admin User    | admin@localhost       | admin      | 1
3  | Student 1     | student1@localhost    | student    | 1
4  | Student 2     | student2@localhost    | student    | 1
5  | Student 3     | student3@localhost    | student    | 1
6  | Student 4     | student4@localhost    | student    | 1
7  | Student 5     | student5@localhost    | student    | 1
```

**Login Credentials:** All users have password: `password`

### Controllers

#### Authentication

- [App\Http\Controllers\Auth\LoginController](app/Http/Controllers/Auth/LoginController.php)
    - Login Form Display
    - Login Processing with Validation
    - Account Status Verification (is_active)
    - Automatic Role-Based Redirect
    - Logout with Session Invalidation

#### Dashboard Controllers

- [App\Http\Controllers\Dashboard\SuperAdminDashboardController](app/Http/Controllers/Dashboard/SuperAdminDashboardController.php)
    - Display total users statistics by role
    - Show active users count
- [App\Http\Controllers\Dashboard\AdminDashboardController](app/Http/Controllers/Dashboard/AdminDashboardController.php)
    - Display total users and student count
    - Show active users statistics
- [App\Http\Controllers\Dashboard\StudentDashboardController](app/Http/Controllers/Dashboard/StudentDashboardController.php)
    - Simple welcome dashboard
    - Quick actions menu

### Middleware

#### Role Middleware

- [App\Http\Middleware\CheckRole](app/Http/Middleware/CheckRole.php)
    - Role-Based Access Control (RBAC)
    - Account Active Status Check
    - Redirects inactive users to login
    - Denies non-authorized roles with 403 Forbidden

**Registration in Bootstrap:**

```php
// bootstrap/app.php
$middleware->alias([
    'role' => \App\Http\Middleware\CheckRole::class,
]);
```

### Routes

**Protected Routes Structure:**

```
Public Routes:
  GET  /           - Redirects to login or dashboard
  GET  /login      - Login Form
  POST /login      - Login Processing

Protected Routes (Requires Auth):
  GET  /dashboard  - Role-based redirect

Superadmin Only:
  GET  /dashboard/superadmin  - Superadmin Dashboard

Admin Only:
  GET  /dashboard/admin       - Admin Dashboard

Student Only:
  GET  /dashboard/student     - Student Dashboard

All Authenticated:
  POST /logout     - Logout User
```

**Route Details:**

- Route names: `login`, `login.post`, `logout`, `dashboard`, `dashboard.superadmin`, `dashboard.admin`, `dashboard.student`
- No duplicate routes
- No conflicts
- Proper middleware applied per route

### Views

#### Master Layout

- [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
    - Navigation bar with user info and role badge
    - Alert system for success/error messages
    - Responsive design with Tailwind CSS
    - Logout button

#### Authentication Views

- [resources/views/auth/login.blade.php](resources/views/auth/login.blade.php)
    - Email and password input fields
    - Remember me checkbox
    - Error message display
    - Demo credentials display
    - Responsive login form

#### Dashboard Views

- [resources/views/dashboard/superadmin.blade.php](resources/views/dashboard/superadmin.blade.php)
    - Total users count
    - Breakdown by role (superadmin, admin, student)
    - Active users count
    - System information

- [resources/views/dashboard/admin.blade.php](resources/views/dashboard/admin.blade.php)
    - Total users count
    - Student count
    - Active users statistics

- [resources/views/dashboard/student.blade.php](resources/views/dashboard/student.blade.php)
    - Welcome message
    - User information display
    - Quick action buttons

### Models

#### User Model

- [app/Models/User.php](app/Models/User.php)
- Fillable attributes: name, email, password, role, is_active
- Casts: email_verified_at (datetime), password (hashed), is_active (boolean)
- Implements Authenticatable for session-based authentication

### Security Features

✓ **CSRF Protection** - Enabled by default in Laravel
✓ **Password Hashing** - bcrypt hashing algorithm
✓ **Session-Based Auth** - Secure session management
✓ **Role Middleware** - Fine-grained access control
✓ **Account Status** - Inactive users redirected to login
✓ **Input Validation** - Email and password validation
✓ **SQL Connection** - Secure database credentials management

## Testing

### Test Superadmin Access

```bash
# Login as superadmin
Email: superadmin@localhost
Password: password
# Should redirect to /dashboard/superadmin
```

### Test Admin Access

```bash
# Login as admin
Email: admin@localhost
Password: password
# Should redirect to /dashboard/admin
```

### Test Student Access

```bash
# Login as student
Email: student1@localhost
Password: password
# Should redirect to /dashboard/student
```

### Test Role Protection

```bash
# Attempt to access non-authorized routes
# Students cannot access /dashboard/admin or /dashboard/superadmin
# Should receive 403 Forbidden error
```

### Test Account Deactivation

```bash
# Manually set is_active = false for any user
# Logout and attempt login
# Should receive error message
```

## Files Created/Modified

### Created Files

```
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/Dashboard/SuperAdminDashboardController.php
app/Http/Controllers/Dashboard/AdminDashboardController.php
app/Http/Controllers/Dashboard/StudentDashboardController.php
app/Http/Middleware/CheckRole.php
database/seeders/UserSeeder.php
resources/views/layouts/app.blade.php
resources/views/auth/login.blade.php
resources/views/dashboard/superadmin.blade.php
resources/views/dashboard/admin.blade.php
resources/views/dashboard/student.blade.php
```

### Modified Files

```
database/migrations/0001_01_01_000000_create_users_table.php
database/seeders/DatabaseSeeder.php
app/Models/User.php
routes/web.php
bootstrap/app.php
```

## Verification Checklist

✅ All migrations run successfully
✅ Database seeded with 7 test users
✅ No duplicate routes
✅ No route conflicts
✅ All middleware properly applied
✅ Login functionality works
✅ Role-based redirection works
✅ Role protection middleware works
✅ Account status checking implemented
✅ Blade views render without errors
✅ Composer and npm dependencies installed
✅ Session-based authentication configured

## Next Steps (Optional Features)

- [ ] User Management Interface
- [ ] Role Management Dashboard
- [ ] Permission System
- [ ] Audit Logging
- [ ] Password Reset Functionality
- [ ] Email Verification
- [ ] API Authentication (Token-based)
- [ ] Two-Factor Authentication (2FA)

## Available Commands

```bash
# Development
php artisan serve --host=0.0.0.0 --port=8000
npm run dev

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:rollback

# Routes & Debugging
php artisan route:list
php artisan tinker

# Cache & Configuration
php artisan config:cache
php artisan config:clear
```

---

**Module 1 verified and stable.**
