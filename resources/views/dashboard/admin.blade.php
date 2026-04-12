@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users text-danger" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $totalUsers }}</h3>
                <p class="text-muted mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $totalBookings }}</h3>
                <p class="text-muted mb-0">Total Bookings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-scissors text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $totalBarbers }}</h3>
                <p class="text-muted mb-0">Total Barbers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-cut text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $totalServices }}</h3>
                <p class="text-muted mb-0">Total Services</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <a href="{{ route('admin.services.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-cut text-success" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3 text-dark">Manage Services</h5>
                <p class="text-muted small mb-0">Tambah, edit, hapus layanan</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.gallery.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-images text-info" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3 text-dark">Manage Gallery</h5>
                <p class="text-muted small mb-0">Kelola foto galeri</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.users.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-users text-danger" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3 text-dark">Manage Users</h5>
                <p class="text-muted small mb-0">Kelola user & role</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Bookings -->
<h5 class="fw-bold mb-3">Recent Bookings</h5>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Barber</th>
                    <th>Cabang</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>{{ $booking->user->name ?? '-' }}</td>
                    <td>{{ $booking->barber->user->name ?? '-' }}</td>
                    <td>{{ $booking->barber->branch->name ?? '-' }}</td>
                    <td>{{ $booking->service->name ?? '-' }}</td>
                    <td>{{ $booking->booking_date->format('d M Y') }} {{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                    <td>
                        @switch($booking->status)
                            @case('pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                                @break
                            @case('confirmed')
                                <span class="badge bg-success">Confirmed</span>
                                @break
                            @case('completed')
                                <span class="badge bg-info">Completed</span>
                                @break
                            @case('cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                                @break
                        @endswitch
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada booking.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recentBookings->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
        {{ $recentBookings->onEachSide(1)->links() }}
    </div>
    @endif
</div>
@endsection
