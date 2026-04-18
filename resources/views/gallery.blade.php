@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
<div class="row mb-5">
    <div class="col-lg-8">
        <h1 class="fw-bold mb-3">Our Gallery</h1>
        <p class="lead text-muted">Lihat hasil karya terbaik dari tim barber profesional kami.</p>
    </div>
</div>

<!-- Gallery Filter -->
<div class="row mb-4">
    <div class="col-12">
        <p class="text-muted">Total: {{ count($galleries) }} foto</p>
    </div>
</div>

<!-- Gallery Grid -->
<div class="row g-3">
    @if($galleries && count($galleries) > 0)
        @foreach($galleries as $gallery)
        <div class="col-md-6 col-lg-4 gallery-item">
            <div class="position-relative rounded overflow-hidden" style="background: #111; min-height: 260px; cursor: pointer;"
                 data-bs-toggle="modal" data-bs-target="#galleryModal" 
                 onclick="updateModal('{{ asset('storage/gallery/' . $gallery->image) }}', '{{ $gallery->title }}')">
                <img src="{{ asset('storage/gallery/' . $gallery->image) }}" 
                     alt="{{ $gallery->title }}" 
                     class="img-fluid"
                     style="width: 100%; height: 100%; max-height: 350px; object-fit: contain; display: block; margin: auto;"
                     onerror="this.src='{{ asset('images/logo.png') }}'; this.style.opacity='0.3';">
                
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-0 d-flex align-items-end p-3"
                     style="transition: all 0.3s ease;">
                    <div class="text-white">
                        <h5 class="mb-1">{{ $gallery->title }}</h5>
                    </div>
                </div>

                <div class="position-absolute top-50 start-50 translate-middle" style="transition: all 0.3s ease;">
                    <i class="fas fa-search-plus text-white" style="font-size: 2rem; opacity: 0; transition: opacity 0.3s ease;"></i>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="fas fa-images" style="font-size: 3rem;"></i>
                <p class="mt-3">Belum ada gallery tersedia.</p>
            </div>
        </div>
    @endif
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title" id="modalTitle">Gallery Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img id="modalImage" src="" alt="" class="img-fluid w-100" onerror="this.src='{{ asset('images/logo.png') }}'; this.style.opacity='0.3';">
            </div>
        </div>
    </div>
</div>

<script>
    // Modal update function dengan error handling
    function updateModal(image, title) {
        const img = document.getElementById('modalImage');
        img.src = image;
        img.onerror = function() {
            this.src = '{{ asset('images/logo.png') }}';
            this.style.opacity = '0.3';
        };
        document.getElementById('modalTitle').textContent = title;
    }

    // Hover effect
    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            const img = this.querySelector('img');
            const overlay = this.querySelector('.bg-dark');
            const icon = this.querySelector('.fa-search-plus');
            
            if (img) img.style.transform = 'scale(1.05)';
            if (overlay) overlay.style.cssText = 'background-color: rgba(0, 0, 0, 0.3) !important;';
            if (icon) icon.style.opacity = '1';
        });
        
        item.addEventListener('mouseleave', function() {
            const img = this.querySelector('img');
            const overlay = this.querySelector('.bg-dark');
            const icon = this.querySelector('.fa-search-plus');
            
            if (img) img.style.transform = 'scale(1)';
            if (overlay) overlay.style.cssText = 'background-color: rgba(0, 0, 0, 0) !important;';
            if (icon) icon.style.opacity = '0';
        });
    });
</script>
@endsection
