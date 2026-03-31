# Authentication System Code Reference

## 1. AuthController Complete Code

```php
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Show register form
    public function showRegister() { }

    // Handle registration
    public function register(Request $request) { }

    // Show login form
    public function showLogin() { }

    // Handle login
    public function login(Request $request) { }

    // Handle logout
    public function logout(Request $request) { }

    // Show dashboard
    public function dashboard() { }
}
```

## 2. User Model Role Methods

```php
<?php
namespace App\Models;

class User extends Authenticatable
{
    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is barber
    public function isBarber(): bool
    {
        return $this->role === 'barber';
    }

    // Check if user is customer
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // Relations
    public function barber(): HasOne
    {
        return $this->hasOne(Barber::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
```

## 3. Middleware Implementation

### CheckRole Middleware

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access');
    }
}
```

### CheckAuthenticated Middleware

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
```

## 4. Middleware Registration

**File: `bootstrap/app.php`**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'auth.check' => \App\Http\Middleware\CheckAuthenticated::class,
    ]);
})
```

## 5. Routes Configuration

**File: `routes/web.php`**

### Public Routes

```php
// Public access
Route::get('/', function () {
    return view('home');
})->name('home');

// Guest only (unauthenticated)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
```

### Protected Routes

```php
// Authenticated only
Route::middleware('auth.check')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});
```

### Role-Based Routes

```php
// Admin only
Route::middleware(['auth.check', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

// Barber only
Route::middleware(['auth.check', 'role:barber'])->prefix('barber')->name('barber.')->group(function () {
    Route::get('/dashboard', [BarberController::class, 'dashboard'])->name('dashboard');
});

// Customer only
Route::middleware(['auth.check', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
});

// Multiple roles
Route::middleware(['auth.check', 'role:admin,barber'])->group(function () {
    // Admin dan Barber routes
});
```

## 6. Blade View Usage

### Check Authentication

```blade
@auth
    <!-- User is logged in -->
    <p>Hello {{ Auth::user()->name }}</p>
@endauth

@guest
    <!-- User is not logged in -->
    <p>Please login first</p>
@endguest
```

### Check Roles

```blade
@if(Auth::user()->isAdmin())
    <!-- Admin only content -->
@endif

@if(Auth::user()->isBarber())
    <!-- Barber only content -->
@endif

@if(Auth::user()->isCustomer())
    <!-- Customer only content -->
@endif
```

### Get User Info

```blade
{{ Auth::user()->name }}          <!-- User name -->
{{ Auth::user()->email }}         <!-- User email -->
{{ Auth::user()->role }}          <!-- User role -->
{{ Auth::id() }}                  <!-- User ID -->
```

### Logout Form

```blade
<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-danger">Logout</button>
</form>
```

## 7. Controller Usage Examples

### Access Authenticated User

```php
public function dashboard()
{
    $user = Auth::user();

    // Check role
    if ($user->isAdmin()) {
        return view('dashboard.admin', ['user' => $user]);
    }

    // Get user info
    echo $user->name;
    echo $user->email;
    echo $user->role;
}
```

### Password Hashing

```php
public function register(Request $request)
{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
    ]);
}
```

### Validate Credentials

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        // Login successful
        return redirect()->route('dashboard');
    }

    // Login failed
    return back()->withErrors(['email' => 'Invalid credentials']);
}
```

## 8. Session Management

### Login User

```php
Auth::login($user);  // Login user
session()->regenerate();  // Regenerate session
```

### Logout User

```php
Auth::logout();  // Logout user
session()->invalidate();  // Invalidate session
session()->regenerateToken();  // Regenerate CSRF token
```

### Check Authentication

```php
Auth::check();  // Is user authenticated?
Auth::guest();  // Is user guest?
Auth::id();     // Get user ID
Auth::user();   // Get authenticated user
```

## 9. Validation Rules

### Password Validation

```php
$request->validate([
    'password' => [
        'required',
        'confirmed',
        Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols(),
    ],
]);
```

### Email Validation

```php
$request->validate([
    'email' => ['required', 'email', 'unique:users'],
]);
```

### Role Validation

```php
$request->validate([
    'role' => ['required', 'in:admin,barber,customer'],
]);
```

## 10. Error Handling

### Display Errors in Views

```blade
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@error('email')
    <span class="text-danger">{{ $message }}</span>
@enderror
```

### Custom Error Messages

```php
$request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
], [
    'email.unique' => 'This email is already registered',
    'password.min' => 'Password must be at least 8 characters',
]);
```

## 11. Flash Messages

### Set Flash Message

```php
return redirect('/')->with('success', 'Login successful!');
return redirect('/')->with('error', 'Login failed!');
```

### Display Flash Message

```blade
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
```

## 12. Database Seeding

### Create User via Seeder

```php
<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}
```

### Run Seeder

```bash
php artisan db:seed --class=UserSeeder
php artisan migrate:fresh --seed
```

## 13. Testing Authentication

### Test Login

```bash
# Login with credentials
curl -X POST http://localhost:8000/login \
  -d "email=admin@example.com&password=password123"
```

### Test Protected Route

```bash
# Access protected route with auth
curl -X GET http://localhost:8000/dashboard \
  -H "Cookie: XSRF-TOKEN=..."
```

## 14. Common Issues & Solutions

### Issue: Login not working

**Solution:**

```php
// Check if user exists
$user = User::where('email', $email)->first();
// Check password
if ($user && Hash::check($password, $user->password)) {
    Auth::login($user);
}
```

### Issue: Session not persisting

**Solution:**

```php
// Regenerate session
session()->regenerate();

// Or set session timeout
'lifetime' => 120, // in config/session.php
```

### Issue: CSRF token mismatch

**Solution:**

```blade
<!-- Always add @csrf to forms -->
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

## 15. Best Practices

✅ **Always hash passwords**

```php
'password' => Hash::make($password)
```

✅ **Regenerate session on login**

```php
session()->regenerate();
```

✅ **Validate input on server**

```php
$request->validate([...]);
```

✅ **Use middleware for protection**

```php
Route::middleware('auth.check')->group([...]);
```

✅ **Check roles before accessing**

```php
if (!Auth::user()->isAdmin()) {
    abort(403);
}
```

✅ **Use named routes**

```blade
{{ route('login') }}  // Instead of '/login'
```

✅ **Use CSRF tokens**

```blade
@csrf  <!-- In all forms -->
```

✅ **Hide sensitive data**

```php
'hidden' => ['password', 'remember_token']
```
