@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="fw-bold">
                <i class="fas fa-tachometer-alt text-danger"></i> My Dashboard
            </h1>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
        <p class="text-muted">Welcome back, <strong>{{ Auth::user()->name }}</strong>! Manage your bookings here.</p>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check text-danger" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">5</h3>
                <p class="text-muted mb-0">Total Bookings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-end text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">2</h3>
                <p class="text-muted mb-0">Upcoming</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">3</h3>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-dollar-sign text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">Rp 280K</h3>
                <p class="text-muted mb-0">Total Spent</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Actions -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="fw-bold mb-4">What would you like to do?</h3>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 hover-effect" style="cursor: pointer; transition: all 0.3s;">
            <div class="card-body text-center p-4">
                <i class="fas fa-calendar-plus text-danger" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-4 fw-bold">Book Appointment</h5>
                <p class="card-text text-muted">Schedule a haircut with your favorite barber</p>
                <a href="{{ route('booking') }}" class="btn btn-danger">Book Now</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 hover-effect" style="cursor: pointer; transition: all 0.3s;">
            <div class="card-body text-center p-4">
                <i class="fas fa-history text-info" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-4 fw-bold">My Bookings</h5>
                <p class="card-text text-muted">View and manage your appointments</p>
                <a href="{{ route('booking') }}" class="btn btn-danger">View All</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 hover-effect" style="cursor: pointer; transition: all 0.3s;">
            <div class="card-body text-center p-4">
                <i class="fas fa-cog text-success" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-4 fw-bold">Account Settings</h5>
                <p class="card-text text-muted">Update your profile information</p>
                <a href="#" class="btn btn-danger">Settings</a>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Bookings -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="fw-bold mb-4">Your Upcoming Bookings</h3>
        @if (true) <!-- Replace with actual booking check -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold">Fade Haircut with Ahmad Rizki</h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-calendar-alt text-danger"></i> March 15, 2024
                                    </p>
                                </div>
                                <span class="badge bg-warning text-dark">Confirmed</span>
                            </div>
                            
                            <hr>
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Time</small>
                                    <strong>02:00 PM</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Duration</small>
                                    <strong>35 minutes</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Price</small>
                                    <strong>Rp 60.000</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Location</small>
                                    <strong>Main Shop</strong>
                                </div>
                            </div>
                            
                            <div class="mt-3 d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-outline-secondary w-50">Reschedule</a>
                                <a href="#" class="btn btn-sm btn-outline-danger w-50">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold">Beard Grooming with Budi Santoso</h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-calendar-alt text-danger"></i> March 18, 2024
                                    </p>
                                </div>
                                <span class="badge bg-info">Pending</span>
                            </div>
                            
                            <hr>
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Time</small>
                                    <strong>10:30 AM</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Duration</small>
                                    <strong>25 minutes</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Price</small>
                                    <strong>Rp 40.000</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Location</small>
                                    <strong>Main Shop</strong>
                                </div>
                            </div>
                            
                            <div class="mt-3 d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-outline-secondary w-50">Reschedule</a>
                                <a href="#" class="btn btn-sm btn-outline-danger w-50">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> You have no upcoming bookings. 
                <a href="{{ route('booking') }}" class="alert-link">Book an appointment now!</a>
            </div>
        @endif
    </div>
</div>

<!-- Favorite Barbers -->
<div class="row">
    <div class="col-12">
        <h3 class="fw-bold mb-4">Your Favorite Barbers</h3>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&h=120&fit=crop" 
                     alt="Ahmad Rizki" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                <h5 class="card-title">Ahmad Rizki</h5>
                <p class="text-muted small">Fade & Modern Cut</p>
                <div class="mb-3">
                    <i class="fas fa-star text-warning"></i>
                    <span class="small">4.8/5 (45 reviews)</span>
                </div>
                <a href="{{ route('booking') }}" class="btn btn-sm btn-danger">Book</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=120&h=120&fit=crop" 
                     alt="Budi Santoso" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                <h5 class="card-title">Budi Santoso</h5>
                <p class="text-muted small">Classic & Beard</p>
                <div class="mb-3">
                    <i class="fas fa-star text-warning"></i>
                    <span class="small">4.7/5 (32 reviews)</span>
                </div>
                <a href="{{ route('booking') }}" class="btn btn-sm btn-danger">Book</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=120&h=120&fit=crop" 
                     alt="Roni Wilianto" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                <h5 class="card-title">Roni Wilianto</h5>
                <p class="text-muted small">Hair Coloring</p>
                <div class="mb-3">
                    <i class="fas fa-star text-warning"></i>
                    <span class="small">4.9/5 (28 reviews)</span>
                </div>
                <a href="{{ route('booking') }}" class="btn btn-sm btn-danger">Book</a>
            </div>
        </div>
    </div>
</div>
@endsection
