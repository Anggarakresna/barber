@extends('layouts.admin')

@section('title', 'Manage Gallery')
@section('page-title', 'Gallery')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Daftar Gallery</h4>
    <a href="{{ route('admin.gallery.create') }}" class="btn btn-danger">
        <i class="fas fa-plus"></i> Tambah Foto
    </a>
</div>

<div class="row g-4">
    @forelse($galleries as $gallery)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <img src="{{ asset('storage/' . $gallery->image) }}" class="card-img-top" alt="{{ $gallery->title }}" style="max-height: 200px; object-fit: contain; background: #f8f9fa;">
            <div class="card-body">
                <h6 class="card-title fw-bold">{{ $gallery->title }}</h6>
                <small class="text-muted">{{ $gallery->created_at->format('d M Y') }}</small>
            </div>
            <div class="card-footer bg-white border-0 d-flex gap-2">
                <a href="{{ route('admin.gallery.edit', $gallery) }}" class="btn btn-sm btn-warning flex-fill">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.gallery.destroy', $gallery) }}" method="POST" class="flex-fill" onsubmit="return confirm('Yakin hapus foto ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-images text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 text-muted">Belum ada foto di gallery.</p>
                <a href="{{ route('admin.gallery.create') }}" class="btn btn-danger">Tambah Foto</a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@if($galleries->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $galleries->links() }}
</div>
@endif
@endsection
