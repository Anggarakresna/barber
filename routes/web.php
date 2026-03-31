<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BarberController as AdminBarberController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\BarberDashboardController;

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes (Authenticated)
Route::middleware('auth.check')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/booking', [BookingController::class, 'create'])->name('booking');
    Route::get('/my-booking', [BookingController::class, 'myBooking'])->name('my-booking');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::patch('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::get('/booking/barbers-by-branch/{branch}', [BookingController::class, 'barbersByBranch'])->name('booking.barbersByBranch');
});

// Admin Routes
Route::middleware(['auth.check', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $totalUsers = \App\Models\User::count();
        $totalBookings = \App\Models\Booking::count();
        $totalBarbers = \App\Models\Barber::count();
        $totalServices = \App\Models\Service::count();
        $recentBookings = \App\Models\Booking::with(['user', 'barber.user', 'barber.branch', 'service'])->latest()->paginate(5);
        return view('dashboard.admin', compact('totalUsers', 'totalBookings', 'totalBarbers', 'totalServices', 'recentBookings'));
    })->name('dashboard');

    // Services CRUD
    Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [AdminServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [AdminServiceController::class, 'destroy'])->name('services.destroy');

    // Gallery CRUD
    Route::get('/gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/create', [AdminGalleryController::class, 'create'])->name('gallery.create');
    Route::post('/gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
    Route::get('/gallery/{gallery}/edit', [AdminGalleryController::class, 'edit'])->name('gallery.edit');
    Route::put('/gallery/{gallery}', [AdminGalleryController::class, 'update'])->name('gallery.update');
    Route::delete('/gallery/{gallery}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');

    // Barbers CRUD
    Route::get('/barbers', [AdminBarberController::class, 'index'])->name('barbers.index');
    Route::get('/barbers/create', [AdminBarberController::class, 'create'])->name('barbers.create');
    Route::post('/barbers', [AdminBarberController::class, 'store'])->name('barbers.store');
    Route::get('/barbers/{barber}/edit', [AdminBarberController::class, 'edit'])->name('barbers.edit');
    Route::put('/barbers/{barber}', [AdminBarberController::class, 'update'])->name('barbers.update');
    Route::delete('/barbers/{barber}', [AdminBarberController::class, 'destroy'])->name('barbers.destroy');
    Route::patch('/barbers/{barber}/toggle-status', [AdminBarberController::class, 'toggleStatus'])->name('barbers.toggleStatus');

    // Branches CRUD
    Route::get('/branches', [AdminBranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/create', [AdminBranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [AdminBranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [AdminBranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{branch}', [AdminBranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [AdminBranchController::class, 'destroy'])->name('branches.destroy');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

// Barber Routes
Route::middleware(['auth.check', 'role:barber'])->prefix('barber')->name('barber.')->group(function () {
    Route::get('/dashboard', [BarberDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/bookings/{booking}/status', [BarberDashboardController::class, 'updateStatus'])->name('bookings.updateStatus');
});

// Public Website Routes (Customer can access these)
Route::get('/services', function () {
    $services = \App\Models\Service::latest()->get();
    return view('services', compact('services'));
})->name('services');

Route::get('/barbers', function () {
    $barbers = \App\Models\Barber::with('user')->latest()->get();
    return view('barbers', compact('barbers'));
})->name('barbers');

Route::get('/gallery', function () {
    $galleries = \App\Models\Gallery::latest()->get();
    return view('gallery', compact('galleries'));
})->name('gallery');
