# 🚀 Barbershop Authentication System - Quick Start Guide

## Instalasi Cepat

### 1️⃣ Jalankan Migration

```bash
php artisan migrate:fresh
```

### 2️⃣ Seed Demo Data (Optional)

```bash
php artisan db:seed --class=UserSeeder
```

Atau langsung dengan migrate:

```bash
php artisan migrate:fresh --seed
```

### 3️⃣ Start Server

```bash
php artisan serve
```

Server akan berjalan di: `http://localhost:8000`

---

## 📝 Demo Accounts

Gunakan akun berikut untuk testing:

### Admin Account

```
Email: admin@barbershop.com
Password: password123
```

**Access**: `/dashboard` → Admin Dashboard

### Barber Account

```
Email: ahmad@barbershop.com
Password: password123
```

**Access**: `/dashboard` → Barber Dashboard

### Customer Account

```
Email: john@example.com
Password: password123
```

**Access**: `/dashboard` → Customer Dashboard

---

## 🌐 Navigation

### Public Pages

- `/` - Homepage
- `/services` - View services
- `/barbers` - View barbers
- `/gallery` - Photo gallery
- `/booking` - Book appointment
- `/contact` - Contact us

### Authentication Pages

- `/register` - Create new account
- `/login` - Login to account

### Protected Pages

- `/dashboard` - User dashboard (role-based redirect)
- `/logout` - Logout (POST)

### Admin Routes

- `/admin/dashboard` - Admin dashboard
- `/admin/users` - Manage users

### Barber Routes

- `/barber/dashboard` - Barber dashboard
- `/barber/bookings` - Manage bookings

### Customer Routes

- `/customer/dashboard` - Customer dashboard
- `/customer/bookings` - My bookings

---

## 🔐 User Roles

### Admin Role

✅ Lihat semua users
✅ Manage barbers
✅ Manage services
✅ View reports
✅ Full system control

### Barber Role

✅ Manage personal schedule
✅ Accept/reject bookings
✅ View customer bookings
✅ Set working hours
✅ Request days off

### Customer Role

✅ Book appointments
✅ Manage personal bookings
✅ View barber profiles
✅ Rate and review
✅ Update profile

---

## 📁 File Structure

```
app/
├── Http/Controllers/AuthController.php      # Auth logic
├── Middleware/
│   ├── CheckRole.php                        # Role checker
│   └── CheckAuthenticated.php               # Auth checker

resources/views/
├── auth/
│   ├── login.blade.php                      # Login form
│   └── register.blade.php                   # Register form
├── dashboard/
│   ├── admin.blade.php                      # Admin dashboard
│   ├── barber.blade.php                     # Barber dashboard
│   └── customer.blade.php                   # Customer dashboard

database/
├── migrations/0001_01_01_000000_create_users_table.php
└── seeders/UserSeeder.php                   # Demo data

routes/web.php                               # All routes
```

---

## 🔄 Registration Process

1. Click **"Sign Up"** button on navbar
2. Fill registration form:
    - Full Name
    - Email
    - Password (min 8 characters)
    - Confirm Password
    - Select Role (Customer or Barber)
3. Click **"Create Account"**
4. Auto-login and redirect to dashboard

---

## 🔓 Login Process

1. Click **"Sign In"** button on navbar
2. Enter credentials:
    - Email
    - Password
3. Optional: Check "Remember me"
4. Click **"Sign In"**
5. Auto-redirect to dashboard based on role

---

## 🚪 Logout

1. Click user dropdown (top right navbar)
2. Select **"Logout"** atau
3. Make POST request to `/logout`

---

## ✨ Features

✅ **Password Hashing** - Bcrypt encryption
✅ **CSRF Protection** - Built-in token security
✅ **Session Management** - Secure session handling
✅ **Role-Based Access** - Middleware protection
✅ **Input Validation** - Server-side validation
✅ **Error Handling** - User-friendly messages
✅ **Remember Me** - Login persistence
✅ **Responsive Design** - Mobile & desktop friendly

---

## 🛠️ Helpful Commands

### Create New User (Artisan)

```bash
php artisan tinker
User::create(['name' => 'John', 'email' => 'john@example.com', 'password' => Hash::make('password123'), 'role' => 'customer']);
exit
```

### Check User

```bash
php artisan tinker
User::where('email', 'admin@barbershop.com')->first();
exit
```

### Reset Database

```bash
php artisan migrate:fresh
```

### Reseed Database

```bash
php artisan db:seed --class=UserSeeder
```

---

## 🐛 Troubleshooting

### Login tidak bekerja?

- Pastikan user terdaftar di database
- Check email dan password benar
- Clear browser cache dan cookies

### Middleware error?

- Pastikan middleware registered di `bootstrap/app.php`
- Check route configuration di `routes/web.php`

### Seeder gagal?

- Run migration terlebih dahulu: `php artisan migrate:fresh`
- Pastikan UserSeeder.php ada di correct location
- Run: `php artisan db:seed --class=UserSeeder`

### Dashboard tidak muncul?

- Check apakah sudah login dengan benar
- Pastikan user memiliki role yang sesuai
- Check browser console untuk error messages

---

## 📚 Documentation

Untuk dokumentasi lengkap, lihat [AUTHENTICATION_DOCS.md](AUTHENTICATION_DOCS.md)

---

## 🎯 Next Steps

1. ✅ Customize login/register form dengan logo
2. ✅ Add email verification
3. ✅ Implement password reset
4. ✅ Add activity logging
5. ✅ Create profile management page
6. ✅ Add two-factor authentication

---

## 📞 Support

Jika ada yang tidak jelas atau error, check:

- [AUTHENTICATION_DOCS.md](AUTHENTICATION_DOCS.md) - Full documentation
- `app/Http/Controllers/AuthController.php` - Controller logic
- `routes/web.php` - Route definitions
- `bootstrap/app.php` - Middleware configuration

Happy Coding! 🚀
