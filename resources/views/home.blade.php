@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="row align-items-center min-vh-50">
    <!-- Hero Text -->
    <div class="col-lg-6 mb-4 mb-lg-0">
        <h1 class="display-4 fw-bold mb-3">
            Premium Barbershop Experience
        </h1>
        <p class="lead text-muted mb-4">
            Dapatkan potongan rambut terbaik dari barber berpengalaman kami. Kami menawarkan layanan grooming
            berkualitas tinggi dengan sentuhan profesional.
        </p>
        <div class="d-flex gap-3 flex-wrap">
            <a href="{{ route('booking') }}" class="btn btn-sign-in btn-lg">
                <i class="fas fa-calendar-alt"></i> Book Appointment
            </a>
            <a href="{{ route('services') }}" class="btn btn-sign-up btn-lg">
                <i class="fas fa-scissors"></i> View Services
            </a>
        </div>
    </div>
</div>

<!-- Services Preview -->
<section class="mt-5 pt-5">
    <h2 class="text-center fw-bold mb-5">Our Services</h2>
    <div class="row g-4">
        @forelse($services as $service)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                @if($service->image)
                <img src="{{ asset('storage/' . $service->image) }}" class="card-img-top" alt="{{ $service->name }}"
                    style="height: 200px; object-fit: cover;">
                @endif
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="fas fa-cut text-danger"></i> {{ $service->name }}</h5>
                    <p class="card-text text-muted">{{ Str::limit($service->description, 80) ?? '' }}</p>
                    <p class="fw-bold text-danger fs-5">Rp {{ number_format($service->price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center text-muted">
            <p>Belum ada service tersedia.</p>
        </div>
        @endforelse
    </div>
    <div class="text-center mt-4">
        <a href="{{ route('services') }}" class="btn btn-outline-danger btn-lg">
            View All Services <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- Our Barbers -->
<section class="mt-5 pt-5">
    <h2 class="text-center fw-bold mb-5">Our Professional Barbers</h2>
    <div class="row g-4">
        @forelse($barbers as $barber)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    @if($barber->photo)
                    <img src="{{ asset('storage/' . $barber->photo) }}" alt="{{ $barber->user->name }}"
                        class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                        style="width:150px;height:150px;">
                        <i class="fas fa-user" style="font-size: 3rem;"></i>
                    </div>
                    @endif
                    <h5 class="card-title fw-bold">{{ $barber->user->name }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($barber->bio, 100) ?? '' }}</p>
                    @if($barber->is_active)
                    <span class="badge bg-success">Aktif</span>
                    @else
                    <span class="badge bg-secondary">Libur</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        @endforelse
    </div>
    @if($barbers->count())
    <div class="text-center mt-4">
        <a href="{{ route('barbers') }}" class="btn btn-outline-danger btn-lg">
            View All Barbers <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
    @endif
</section>

<!-- Gallery Preview -->
<section class="mt-5 pt-5">
    <h2 class="text-center fw-bold mb-5">Our Gallery</h2>
    <div class="row g-3">
        @forelse($galleries as $gallery)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div
                    style="height: 220px; background: #111; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('storage/' . $gallery->image) }}" class="img-fluid" alt="{{ $gallery->title }}"
                        style="max-height: 220px; max-width: 100%; object-fit: contain;">
                </div>
                <div class="card-body text-center py-2">
                    <small class="fw-bold">{{ $gallery->title }}</small>
                </div>
            </div>
        </div>
        @empty
        @endforelse
    </div>
    @if($galleries->count())
    <div class="text-center mt-4">
        <a href="{{ route('gallery') }}" class="btn btn-outline-danger btn-lg">
            View Gallery <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
    @endif
</section>

<!-- CTA Section -->
<section class="mt-5 pt-5 bg-light rounded p-5 text-center">
    <h2 class="fw-bold mb-3">Ready for Your Next Haircut?</h2>
    <p class="lead text-muted mb-4">Booking sekarang dan dapatkan potongan rambut terbaik dari barber profesional kami!
    </p>
    <a href="{{ route('booking') }}" class="btn btn-danger btn-lg">
        <i class="fas fa-calendar-plus"></i> Book Now
    </a>
</section>
@endsection