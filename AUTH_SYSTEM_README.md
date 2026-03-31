# Barbershop Authentication System 🚀

Sistem authentication lengkap dengan role-based access control untuk website booking barbershop menggunakan Laravel tanpa Breeze.

## ✨ Fitur Utama

- ✅ **Manual Authentication System** - Tanpa Breeze atau Sanctum
- ✅ **Role-Based Access Control** - Admin, Barber, Customer
- ✅ **Secure Password Hashing** - Bcrypt encryption
- ✅ **Session Management** - Login/Logout dengan session handling
- ✅ **Middleware Protection** - Role checking & authentication
- ✅ **Form Validation** - Server-side input validation
- ✅ **CSRF Protection** - Built-in token security
- ✅ **Responsive Dashboard** - Untuk setiap role
- ✅ **Remember Me** - Login persistence
- ✅ **Error Handling** - User-friendly messages
- ✅ **Modern UI** - Bootstrap 5 design

---

## 🎯 Roles & Capabilities

### 👨‍💼 Admin

- Mengelola semua users
- Mengelola barber profiles
- Mengelola services
- Melihat reports dan analytics
- Full system control

### ✂️ Barber

- Manage personal schedule
- Accept/Reject bookings
- Set working hours
- Request days off
- View customer bookings

### 👤 Customer

- Book appointments
- Manage personal bookings
- View barber profiles
- Rate and review
- Update profile

---

## 📋 File Structure

```
project/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AuthController.php          ✅ Authentication logic
│   │   └── Middleware/
│   │       ├── CheckRole.php               ✅ Role verification
│   │       └── CheckAuthenticated.php      ✅ Auth verification
│   └── Models/
│       └── User.php                        ✅ User model dengan methods
│
├── resources/views/
│   ├── auth/
│   │   ├── login.blade.php                 ✅ Login form
│   │   └── register.blade.php              ✅ Register form
│   ├── dashboard/
│   │   ├── admin.blade.php                 ✅ Admin dashboard
│   │   ├── barber.blade.php                ✅ Barber dashboard
│   │   └── customer.blade.php              ✅ Customer dashboard
│   ├── layouts/
│   │   └── app.blade.php                   ✅ Main layout
│   ├── home.blade.php
│   ├── services.blade.php
│   ├── barbers.blade.php
│   ├── gallery.blade.php
│   ├── booking.blade.php
│   └── contact.blade.php
│
├── database/
│   ├── migrations/
│   │   └── 0001_01_01_000000_create_users_table.php
│   └── seeders/
│       └── UserSeeder.php                  ✅ Demo data
│
├── routes/
│   └── web.php                             ✅ All routes
│
├── bootstrap/
│   └── app.php                             ✅ Middleware config
│
├── QUICK_START.md                          📖 Setup guide
├── AUTHENTICATION_DOCS.md                  📖 Full documentation
├── CODE_REFERENCE.md                       📖 Code examples
└── README.md                               📖 This file
```

---

## 🚀 Quick Start

### 1. Run Migration

```bash
php artisan migrate:fresh
```

### 2. Seed Demo Data

```bash
php artisan db:seed --class=UserSeeder
```

### 3. Start Server

```bash
php artisan serve
```

### 4. Access System

- Homepage: `http://localhost:8000`
- Login: `http://localhost:8000/login`
- Register: `http://localhost:8000/register`

---

## 🔐 Demo Accounts

| Role     | Email                | Password    |
| -------- | -------------------- | ----------- |
| Admin    | admin@barbershop.com | password123 |
| Barber   | ahmad@barbershop.com | password123 |
| Customer | john@example.com     | password123 |

---

## 📖 Documentation

Dokumentasi lengkap tersedia dalam 3 file:

### 1. **QUICK_START.md**

- Setup instructions
- Demo accounts
- Navigation guide
- Common commands

### 2. **AUTHENTICATION_DOCS.md**

- Complete feature overview
- Routes documentation
- Components explanation
- Best practices
- Troubleshooting

### 3. **CODE_REFERENCE.md**

- Complete code examples
- Implementation details
- Usage patterns
- Testing guide

---

## 🔑 Key Components

### AuthController

Menangani semua authentication logic:

- User registration
- User login
- User logout
- Dashboard routing

**Location:** `app/Http/Controllers/AuthController.php`

### Middleware

- **CheckRole** - Verifikasi role user
- **CheckAuthenticated** - Verifikasi user login

**Location:** `app/Http/Middleware/`

### User Model

Extended features:

- `isAdmin()` - Check if admin
- `isBarber()` - Check if barber
- `isCustomer()` - Check if customer
- Relations: `barber()`, `bookings()`

**Location:** `app/Models/User.php`

### Routes

Fully organized dengan middleware:

- Public routes (home, services, etc)
- Auth routes (login, register)
- Protected routes (dashboard, logout)
- Role-based routes (admin/_, barber/_, customer/\*)

**Location:** `routes/web.php`

---

## 🎨 Views

### Authentication Views

- `auth/login.blade.php` - Login form dengan demo accounts
- `auth/register.blade.php` - Register form dengan role selection

### Dashboard Views

- `dashboard/admin.blade.php` - Admin dashboard dengan stats
- `dashboard/barber.blade.php` - Barber dashboard dengan schedule
- `dashboard/customer.blade.php` - Customer dashboard dengan bookings

### Layout

- `layouts/app.blade.php` - Main layout dengan navbar responsive

---

## 🔄 Authentication Flow

```
User Access → Check Guest Middleware
                    ↓
            [Login/Register]
                    ↓
            Validate Credentials
                    ↓
            Create/Check User
                    ↓
            Hash Password & Store
                    ↓
            Auto Login User
                    ↓
            Check Dashboard Route
                    ↓
            Match User Role
                    ↓
            Redirect to Role Dashboard
```

---

## 🛡️ Security Features

- ✅ **Password Hashing** dengan bcrypt
- ✅ **CSRF Token** protection pada semua form
- ✅ **Session Regeneration** setelah login
- ✅ **Input Validation** server-side
- ✅ **SQL Injection Prevention** dengan Eloquent
- ✅ **XSS Prevention** dengan Blade escaping
- ✅ **Middleware Protection** pada private routes
- ✅ **Role Verification** pada setiap route
- ✅ **Secure Session** dengan HTTP-only cookies

---

## 🧪 Testing Routes

### Public Routes (tanpa login)

```
GET  /                    → Homepage
GET  /services            → Services page
GET  /barbers             → Barbers page
GET  /gallery             → Gallery page
GET  /booking             → Booking page
GET  /contact             → Contact page
GET  /register            → Register form
GET  /login               → Login form
```

### Protected Routes (perlu login)

```
POST /login               → Handle login
POST /register            → Handle registration
POST /logout              → Handle logout
GET  /dashboard           → User dashboard
```

### Admin Routes

```
GET  /admin/dashboard     → Admin dashboard
GET  /admin/users         → Manage users
```

### Barber Routes

```
GET  /barber/dashboard    → Barber dashboard
GET  /barber/bookings     → Barber bookings
```

### Customer Routes

```
GET  /customer/dashboard  → Customer dashboard
GET  /customer/bookings   → Customer bookings
```

---

## 📝 Configuration Files

### `bootstrap/app.php`

Middleware registration:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\CheckRole::class,
    'auth.check' => \App\Http\Middleware\CheckAuthenticated::class,
]);
```

### `routes/web.php`

Route organization:

```php
// Public routes
Route::get('/', ...);

// Guest routes
Route::middleware('guest')->group([...]);

// Protected routes
Route::middleware('auth.check')->group([...]);

// Role-based routes
Route::middleware(['auth.check', 'role:admin'])->group([...]);
```

---

## 💻 Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)

---

## 🐛 Common Issues

### Login tidak bekerja?

1. Pastikan migration sudah jalan: `php artisan migrate`
2. Check database connection
3. Verify email & password benar
4. Check browser console untuk errors

### Middleware error?

1. Pastikan middleware registered di `bootstrap/app.php`
2. Check route config di `routes/web.php`
3. Verify user role di database

### Seeder gagal?

1. Run migration terlebih dahulu
2. Check seeder path
3. Run: `php artisan db:seed --class=UserSeeder`

Untuk troubleshooting lengkap, lihat **AUTHENTICATION_DOCS.md**

---

## 🚀 Production Checklist

Sebelum deploy ke production:

- [ ] Change default passwords
- [ ] Configure mail server
- [ ] Add email verification
- [ ] Setup password reset
- [ ] Enable two-factor auth (optional)
- [ ] Configure rate limiting
- [ ] Setup logging
- [ ] Configure HTTPS
- [ ] Add security headers
- [ ] Setup backup strategy
- [ ] Test all authentication flows
- [ ] Performance optimization

---

## 🔄 Maintenance

### Update User

```bash
php artisan tinker
$user = User::find(1);
$user->update(['name' => 'New Name']);
```

### Reset Password

```bash
php artisan tinker
$user = User::find(1);
$user->update(['password' => Hash::make('newpassword')]);
```

### Create New User

```bash
php artisan make:seeder CreateUserSeeder
```

---

## 📞 Support Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Bootstrap Documentation**: https://getbootstrap.com/docs
- **PHP Documentation**: https://www.php.net/docs.php

---

## 📦 Dependencies

- Laravel 11.x
- PHP 8.1+
- Bootstrap 5.3
- Font Awesome 6.4

---

## 📄 License

Open source - Feel free to use and modify

---

## 🎓 Learning Resources

Dari implementasi ini Anda bisa belajar tentang:

- Laravel authentication patterns
- Middleware implementation
- Role-based access control
- Session management
- Security best practices
- Form validation
- Database seeding
- Blade templating
- Route organization

---

## 🎉 Enjoy!

Sistem authentication lengkap dan siap digunakan. Silakan customize sesuai kebutuhan Anda.

Untuk pertanyaan atau masalah, check dokumentasi di:

- **QUICK_START.md** - Setup & overview
- **AUTHENTICATION_DOCS.md** - Detailed documentation
- **CODE_REFERENCE.md** - Code examples

Happy coding! 🚀

---

**Last Updated:** March 2024
**Version:** 1.0.0
**Status:** Production Ready ✅
