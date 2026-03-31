<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar {
            min-height: 100vh;
            background-color: #1a1a1a;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 100;
            padding-top: 0;
        }
        .sidebar .brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.4rem;
            font-weight: 700;
            border-bottom: 1px solid #333;
        }
        .sidebar .brand span { color: #DC3545; }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            transition: all .2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #DC3545;
        }
        .sidebar .nav-link i { width: 24px; text-align: center; margin-right: 8px; }
        .main-content { margin-left: 250px; min-height: 100vh; background-color: #f8f9fa; }
        .topbar {
            background: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content-body { padding: 2rem; }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
        /* Pagination styling */
        .card-footer .pagination,
        .d-flex.justify-content-center .pagination {
            margin-bottom: 0;
        }
        .card-footer nav,
        .d-flex.justify-content-center nav {
            width: auto;
        }
        .page-link {
            color: #DC3545;
            border-color: #dee2e6;
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
        }
        .page-link:hover {
            color: #fff;
            background-color: #DC3545;
            border-color: #DC3545;
        }
        .page-item.active .page-link {
            background-color: #DC3545;
            border-color: #DC3545;
            color: #fff;
        }
        .page-item.disabled .page-link {
            color: #adb5bd;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}" class="brand">
                <img src="{{ asset('images/logo2.png') }}" alt="Barbershop Logo" style="height: 100px; width: auto; object-fit: contain;">
            </a>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                    <i class="fas fa-cut"></i> Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.barbers.*') ? 'active' : '' }}" href="{{ route('admin.barbers.index') }}">
                    <i class="fas fa-user-tie"></i> Barbers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.branches.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}">
                    <i class="fas fa-store"></i> Branches
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.gallery.*') ? 'active' : '' }}" href="{{ route('admin.gallery.index') }}">
                    <i class="fas fa-images"></i> Gallery
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-globe"></i> View Website
                </a>
            </li>
            <li class="nav-item">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start" style="color:#adb5bd;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h5 class="mb-0">@yield('page-title', 'Admin Panel')</h5>
            <div>
                <span class="text-muted">Welcome,</span> <strong>{{ Auth::user()->name }}</strong>
            </div>
        </div>
        <div class="content-body">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
