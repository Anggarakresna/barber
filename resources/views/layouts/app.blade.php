<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" sizes="16x16" href="{{ asset('favicon.ico') }}">

    <title>@yield('title', 'Barbershop') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #333;
        }

        /* Navbar Styles */
        .navbar-barbershop {
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: relative;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: #000 !important;
            letter-spacing: -0.5px;
        }

        .navbar-brand span {
            color: #DC3545;
        }

        /* Navigation Links */
        .navbar-nav .nav-link {
            color: #555 !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 0.75rem !important;
            position: relative;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #DC3545 !important;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: #DC3545;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 70%;
        }

        .navbar-nav .nav-link.active {
            color: #DC3545 !important;
            font-weight: 600;
        }

        /* Auth Buttons */
        .btn-sign-up {
            color: #333;
            border: 2px solid #333;
            background-color: transparent;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 4px;
            margin-right: 0.75rem;
        }

        .btn-sign-up:hover {
            background-color: #f5f5f5;
            color: #333;
            border-color: #333;
        }

        .btn-sign-in {
            background-color: #DC3545;
            color: white !important;
            border: 2px solid #DC3545;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-sign-in:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        /* Hamburger Menu */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.75rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: 2px solid #DC3545;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23333' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            width: 1.5rem;
            height: 1.5rem;
        }

        /* Navbar Collapse */
        .navbar-collapse {
            transition: all 0.3s ease;
        }

        .navbar-collapse.show {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .navbar-nav {
                padding-top: 1rem;
                border-top: 1px solid #eee;
                margin-top: 1rem;
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 0 !important;
                margin: 0 !important;
            }

            .navbar-nav .nav-link::after {
                display: none;
            }

            .navbar-nav .nav-link:hover {
                color: #DC3545 !important;
            }

            .auth-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                padding-top: 1rem;
                border-top: 1px solid #eee;
                margin-top: 1rem;
            }

            .btn-sign-up,
            .btn-sign-in {
                margin-right: 0;
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 575px) {
            .navbar-brand {
                font-size: 1.5rem;
            }

            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        /* Active page indicator */
        .nav-link.active {
            border-bottom: 2px solid #DC3545;
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 80px);
            padding: 2rem 0;
        }

        /* Footer */
        .footer-barbershop {
            background-color: #1a1a1a;
            color: #fff;
            padding: 3rem 0 2rem;
            margin-top: 5rem;
        }

        .footer-barbershop a {
            color: #DC3545;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-barbershop a:hover {
            color: #fff;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1050;
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg navbar-barbershop">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Barbershop Logo" style="height: 140px; width: auto; object-fit: contain;">
            </a>

            <!-- Hamburger Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Center Menu -->
                <ul class="navbar-nav mx-auto">
                    @auth
                        @if(Auth::user()->role === 'barber')
                            {{-- Navbar Barber: hanya Barber Dashboard --}}
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('barber.dashboard') ? 'active' : '' }}" href="{{ route('barber.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> Barber Dashboard
                                </a>
                            </li>
                        @elseif(Auth::user()->role === 'admin')
                            {{-- Navbar Admin: link ke Admin Panel --}}
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-crown"></i> Admin Dashboard
                                </a>
                            </li>
                        @else
                            {{-- Navbar Customer --}}
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('services') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('barbers') ? 'active' : '' }}" href="{{ route('barbers') }}">Barbers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('gallery') ? 'active' : '' }}" href="{{ route('gallery') }}">Gallery</a>
                            </li>
        
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('booking') ? 'active' : '' }}" href="{{ route('booking') }}">Booking</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('my-booking') ? 'active' : '' }}" href="{{ route('my-booking') }}">My Booking</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::is('feedback.*') ? 'active' : '' }}" href="{{ route('feedback.create') }}">Feedback</a>
                            </li>
                        @endif
                    @else
                        {{-- Navbar Guest (belum login) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('services') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('barbers') ? 'active' : '' }}" href="{{ route('barbers') }}">Barbers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('gallery') ? 'active' : '' }}" href="{{ route('gallery') }}">Gallery</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('feedback.*') ? 'active' : '' }}" href="{{ route('feedback.create') }}">Feedback</a>
                        </li>
                    @endauth
                </ul>

                <!-- Right Auth Buttons -->
                <div class="auth-buttons d-flex d-lg-flex gap-2 align-items-lg-center">
                    @auth
                        <!-- Logged In User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" 
                                    id="userMenuDropdown" data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                                <small class="d-block text-muted" style="font-size: 0.75rem;">{{ ucfirst(Auth::user()->role) }}</small>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="w-100">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <!-- Sign Up Button (Outline) -->
                        <a href="{{ route('register') }}" class="btn btn-sign-up">
                            <i class="fas fa-user-plus"></i> Register
                        </a>

                        <!-- Sign In Button (Solid Red) -->
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sign-in">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-barbershop">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-md-4 col-sm-6 mb-4">
                    <h5 class="mb-3">
    <img src="{{ asset('images/logo2.png') }}" alt="Barbershop Logo" height="100">
</h5>
                    <p class="text-muted" style="color: #ccc !important;">
                        Premium barbershop dengan barber berpengalaman dan profesional. Dapatkan potongan rambut terbaik dengan harga terjangkau.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="col-md-4 col-sm-6 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('services') }}">Services</a></li>
                        <li><a href="{{ route('barbers') }}">Barbers</a></li>
                        <li><a href="{{ route('booking') }}">Booking</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-md-4 col-sm-12 mb-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <ul class="list-unstyled">
                        <li>
                            <i class="fas fa-map-marker-alt"></i> 
                            <a href="#">Tunggilis, Jl. Raya Cileungsi - Jonggol, Kec. Cileungsi, Kabupaten Bogor, Jawa Barat 16820</a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i> 
                            <a href="#">Citra indah city No.17 blok BA00, Jonggol, Kabupaten Bogor, Jawa Barat 16830</a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i> 
                            <a href="#">Citra indah city No.17 blok BA00, Jonggol, Kabupaten Bogor, Jawa Barat 16830</a>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i> 
                            <a href="tel:+62123456789">+62 123 456 789</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i> 
                            <a href="mailto:info@barbershop.com">info@cutdory.com</a>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i> 
                            Mon - Fri: 09:00 - 21:00
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="bg-secondary my-4">

            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0" style="color: #ccc !important;">&copy; 2024 BarberShop. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="list-unstyled d-flex justify-content-md-end gap-3">
                        <li><a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
