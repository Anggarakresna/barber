@extends('layouts.app')

@section('title', 'Barbers')

@section('content')
<div class="row mb-5">
    <div class="col-lg-8">
        <h1 class="fw-bold mb-3">Our Professional Barbers</h1>
        <p class="lead text-muted">Tim barber berpengalaman dan profesional siap memberikan layanan terbaik untuk Anda.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($barbers as $barber)
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 text-center overflow-hidden" style="transition: all 0.3s ease;">
            <div class="card-body pb-0">
                @if($barber->photo)
                    <img src="{{ asset('storage/' . $barber->photo) }}" alt="{{ $barber->user->name }}" 
                         class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width:150px;height:150px;">
                        <i class="fas fa-user" style="font-size: 3rem;"></i>
                    </div>
                @endif
                
                <h5 class="card-title fw-bold">{{ $barber->user->name }}</h5>
                
                @if($barber->is_active)
                    <span class="badge bg-success mb-2">Aktif</span>
                @else
                    <span class="badge bg-secondary mb-2">Libur</span>
                @endif

                @if($barber->bio)
                <p class="text-muted small">{{ Str::limit($barber->bio, 80) }}</p>
                @endif
            </div>

            <div class="card-footer bg-light border-0">
                <a href="{{ route('booking') }}" class="btn btn-sm btn-danger w-100">
                    <i class="fas fa-calendar-check"></i> Book Now
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-5">
        <i class="fas fa-user-tie" style="font-size: 3rem;"></i>
        <p class="mt-3">Belum ada barber tersedia.</p>
    </div>
    @endforelse
</div>

<!-- Barber Schedule Info -->
<div class="row mt-5 pt-5">
    <div class="col-12">
        <h3 class="fw-bold mb-4">Barber Availability</h3>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">
                    <i class="fas fa-clock text-danger"></i> Working Hours
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Monday - Friday:</strong> <span class="text-muted">09:00 - 21:00</span>
                    </li>
                    <li class="mb-2">
                        <strong>Saturday:</strong> <span class="text-muted">10:00 - 22:00</span>
                    </li>
                    <li>
                        <strong>Sunday:</strong> <span class="text-muted">10:00 - 20:00</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">
                    <i class="fas fa-info-circle text-danger"></i> Booking Tips
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> Book in advance untuk memastikan slot terbaik
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> Datang 5 menit sebelum jadwal
                    </li>
                    <li>
                        <i class="fas fa-check text-success"></i> Hubungi kami untuk cancel atau reschedule
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
