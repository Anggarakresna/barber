@extends('layouts.app')

@section('title', 'Feedback')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h3 class="fw-bold mb-2">Kritik & Saran</h3>
                <p class="text-muted mb-4">Bantu kami meningkatkan kualitas layanan barbershop.</p>

                <form action="{{ route('feedback.store') }}" method="POST">
                    @csrf

                    @guest
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Masukkan nama Anda"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endguest

                    @auth
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                        <small class="text-muted">Anda mengirim feedback sebagai pengguna login.</small>
                    </div>
                    @endauth

                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select id="rating" name="rating" class="form-select @error('rating') is-invalid @enderror">
                            <option value="">Pilih rating</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ (string) old('rating') === (string) $i ? 'selected' : '' }}>
                                    {{ $i }} bintang
                                </option>
                            @endfor
                        </select>
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label">Pesan Kritik/Saran</label>
                        <textarea
                            id="message"
                            name="message"
                            rows="5"
                            class="form-control @error('message') is-invalid @enderror"
                            placeholder="Tulis kritik atau saran Anda di sini"
                            required>{{ old('message') }}</textarea>
                        <small class="text-muted">Minimal 5 karakter.</small>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: @json(session('success')),
        showConfirmButton: false,
        timer: 3200,
        timerProgressBar: true
    });
</script>
@endif
@endsection
