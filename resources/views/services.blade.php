@extends('layouts.app')

@section('title', 'Services')

@section('content')
<div class="row mb-5">
    <div class="col-lg-8">
        <h1 class="fw-bold mb-3">Our Services</h1>
        <p class="lead text-muted">Kami menyediakan berbagai layanan grooming berkualitas tinggi untuk memenuhi kebutuhan Anda.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($services as $service)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-shadow" style="transition: all 0.3s ease;">
            @if($service->image)
                <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
            @endif
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-cut text-danger" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="card-title fw-bold">{{ $service->name }}</h5>
                <p class="card-text text-muted small">{{ $service->description ?? 'Layanan grooming profesional' }}</p>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <p class="mb-0 small text-muted">
                            <i class="fas fa-clock"></i> {{ $service->duration }} mins
                        </p>
                        <p class="mb-0 fw-bold text-danger fs-5">
                            Rp {{ number_format($service->price, 0, ',', '.') }}
                        </p>
                    </div>
                    <a href="{{ route('booking') }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-calendar"></i> Book
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="fas fa-cut" style="font-size: 3rem;"></i>
            <p class="mt-3">Belum ada layanan tersedia.</p>
        </div>
    </div>
    @endforelse
</div>

@endsection
