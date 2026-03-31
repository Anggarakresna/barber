# Barbershop Authentication System Documentation

## Overview

Sistem authentication manual dengan role-based access control untuk sistem booking barbershop Laravel.

## Roles Tersedia

- **Admin**: Mengelola seluruh sistem (users, barbers, services, reports)
- **Barber**: Mengelola jadwal dan booking pribadi
- **Customer**: Membuat dan mengelola booking

## Fitur Utama

### 1. Authentication Routes

**Public Routes (tanpa login):**

```
GET  /register         - Show registration form
POST /register         - Handle user registration
GET  /login            - Show login form
POST /login            - Handle user login
GET  /                 - Homepage
GET  /services         - Lihat layanan
GET  /barbers          - Lihat daftar barber
GET  /gallery          - Galeri foto
GET  /booking          - Form booking
GET  /contact          - Contact page
```

**Protected Routes (memerlukan login):**

```
GET  /dashboard        - Redirect ke dashboard sesuai role
POST /logout           - Logout user
```

### 2. Role-Based Routes

**Admin Routes** (`/admin`):

```
GET /admin/dashboard   - Admin dashboard
GET /admin/users       - Manage users
```

**Barber Routes** (`/barber`):

```
GET /barber/dashboard  - Barber dashboard
GET /barber/bookings   - Manage bookings
```

**Customer Routes** (`/customer`):

```
GET /customer/dashboard - Customer dashboard
GET /customer/bookings  - My bookings
```

## Komponen Sistem

### 1. User Model (`App\Models\User`)

```php
// Role checking methods
$user->isAdmin();      // Check if user is admin
$user->isBarber();     // Check if user is barber
$user->isCustomer();   // Check if user is customer

// Relations
$user->barber();       // Get barber profile (One-to-One)
$user->bookings();     // Get user bookings (One-to-Many)
```

### 2. Middleware (`App\Http\Middleware`)

**CheckRole Middleware** (`role`):
Mengecek apakah user memiliki role yang sesuai.

Usage:

```php
Route::middleware(['auth.check', 'role:admin,barber'])->group(function () {
    // Routes yang bisa diakses admin dan barber
});
```

**CheckAuthenticated Middleware** (`auth.check`):
Mengecek apakah user sudah login.

### 3. AuthController (`App\Http\Controllers\AuthController`)

Methods:

- `showRegister()` - Tampilkan form registrasi
- `register()` - Handle registrasi user
- `showLogin()` - Tampilkan form login
- `login()` - Handle login
- `logout()` - Handle logout
- `dashboard()` - Redirect ke dashboard sesuai role

### 4. Migration Users Table

Fields:

- id
- name
- email (unique)
- password (hashed)
- role (enum: admin, barber, customer)
- email_verified_at
- remember_token
- timestamps

## Alur Registrasi

1. User mengakses `/register`
2. Pilih role (customer/barber)
3. Isi form dengan data:
    - Name
    - Email
    - Password (min 8 characters)
    - Confirm Password
4. System akan create user dan auto-login
5. Redirect ke `/dashboard`

## Alur Login

1. User mengakses `/login`
2. Masukkan email dan password
3. Optional: checklist "Remember me"
4. System verify credentials
5. Jika berhasil: redirect ke `/dashboard`
6. Jika gagal: tampilkan error message

## Redirect Berdasarkan Role

Setelah login, user akan diarahkan ke dashboard sesuai role mereka:

- Admin → `/dashboard` (admin view)
- Barber → `/dashboard` (barber view)
- Customer → `/dashboard` (customer view)

## Demo Accounts

Untuk testing, gunakan akun-akun berikut:

**Admin:**

- Email: admin@barbershop.com
- Password: password123

**Barber:**

- Email: ahmad@barbershop.com
- Password: password123

**Customer:**

- Email: john@example.com
- Password: password123

## Cara Setup

### 1. Run Migrations

```bash
php artisan migrate:fresh
```

### 2. Seed Demo Data

```bash
php artisan db:seed --class=UserSeeder
```

Atau jika menggunakan `--seed` dengan migrate:

```bash
php artisan migrate:fresh --seed
```

### 3. Start Development Server

```bash
php artisan serve
```

Akses: http://localhost:8000

## File Struktur

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AuthController.php
│   └── Middleware/
│       ├── CheckRole.php
│       └── CheckAuthenticated.php
├── Models/
│   └── User.php

resources/views/
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── dashboard/
│   ├── admin.blade.php
│   ├── barber.blade.php
│   └── customer.blade.php
└── layouts/
    └── app.blade.php

database/
├── migrations/
│   └── 0001_01_01_000000_create_users_table.php
└── seeders/
    └── UserSeeder.php

bootstrap/
└── app.php

routes/
└── web.php
```

## Best Practices Diterapkan

✅ Password hashing dengan bcrypt
✅ CSRF protection dengan @csrf token
✅ Input validation pada form
✅ Role-based access control middleware
✅ Eloquent relationships
✅ Session management
✅ Error messages terstruktur
✅ Login history dengan email_verified_at
✅ Remember me functionality
✅ Proper HTTP status codes

## Keamanan

- Password minimal 8 karakter
- Password hashing dengan bcrypt
- CSRF token pada semua form
- Session regenerate setelah login
- Middleware untuk protected routes
- Role checking pada setiap route
- Input validation pada controller

## Troubleshooting

**Q: Middleware tidak bekerja?**
A: Pastikan sudah register di `bootstrap/app.php`

**Q: Login gagal?**
A: Check apakah email terdaftar dan password benar. Pastikan password_confirmation match.

**Q: Seeder tidak jalan?**
A: Run `php artisan migrate:fresh --seed`

**Q: Role checking tidak bekerja?**
A: Pastikan user memiliki role yang sesuai di database.

## Pengembangan Selanjutnya

Fitur yang bisa ditambahkan:

- Email verification
- Two-factor authentication
- Password reset via email
- Social login (Google, Facebook)
- Profile management
- Activity logging
- Rate limiting on login
- Session timeout management
