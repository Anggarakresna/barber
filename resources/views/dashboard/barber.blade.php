@extends('layouts.app')

@section('title', 'Barber Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="fw-bold">
                <i class="fas fa-tachometer-alt text-danger"></i> Barber Dashboard
            </h1>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
        <p class="text-muted">Welcome, <strong>{{ Auth::user()->name }}</strong>! Manage your schedule and bookings.</p>
        
        @if(!Auth::user()->barber->is_active)
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perhatian!</strong> Akun Anda saat ini sedang tidak aktif. Anda tidak akan menerima pemesanan baru.
            </div>
        @endif
    </div>
</div>

{{-- Flash message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Stats -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check text-danger" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $totalBookings }}</h3>
                <p class="text-muted mb-0">Total Bookings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $completedBookings }}</h3>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-half text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $pendingBookings }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-3 fw-bold">{{ $confirmedBookings }}</h3>
                <p class="text-muted mb-0">Confirmed</p>
            </div>
        </div>
    </div>
</div>

<!-- All Bookings Table -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="fw-bold mb-4">Daftar Booking</h3>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Booking Date</th>
                            <th>Booking Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>{{ ($bookings->currentPage() - 1) * $bookings->perPage() + $loop->iteration }}</td>
                            <td>
                                <i class="fas fa-user-circle text-muted me-1"></i>
                                {{ $booking->user->name }}
                            </td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                            <td>
                                @if($booking->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($booking->status === 'confirmed')
                                    <span class="badge bg-info">Confirmed</span>
                                @elseif($booking->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($booking->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->status === 'pending')
                                    <form action="{{ route('barber.bookings.updateStatus', $booking) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                    </form>
                                    <form action="{{ route('barber.bookings.updateStatus', $booking) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                @elseif($booking->status === 'confirmed')
                                    <form action="{{ route('barber.bookings.updateStatus', $booking) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check-double"></i> Complete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                Belum ada booking untuk Anda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bookings->hasPages())
                <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
                    {{ $bookings->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
