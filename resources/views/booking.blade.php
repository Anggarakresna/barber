@extends('layouts.app')

@section('title', 'Booking')

@section('content')
<div class="row mb-5">
    <div class="col-lg-8">
        <h1 class="fw-bold mb-3">Book An Appointment</h1>
        <p class="lead text-muted">Pilih barber, layanan, dan jam yang Anda inginkan.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ route('booking.store') }}" method="POST" class="card border-0 shadow-sm p-4">
            @csrf

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Branch Selection -->
            <div class="mb-4">
                <label for="branch_id" class="form-label fw-bold">
                    <i class="fas fa-store"></i> Pilih Cabang
                </label>
                <select class="form-select form-select-lg" id="branch_id" name="branch_id" required>
                    <option value="">-- Pilih cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Barber Selection -->
            <div class="mb-4">
                <label for="barber_id" class="form-label fw-bold">
                    <i class="fas fa-user"></i> Select Barber
                </label>
                <select class="form-select form-select-lg" id="barber_id" name="barber_id" required disabled>
                    <option value="">-- Pilih cabang terlebih dahulu --</option>
                </select>
            </div>

            <!-- Service Selection -->
            <div class="mb-4">
                <label for="service_id" class="form-label fw-bold">
                    <i class="fas fa-cut"></i> Select Service
                </label>
                <select class="form-select form-select-lg" id="service_id" name="service_id" required>
                    <option value="">-- Pilih layanan --</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }} ({{ $service->duration }} min)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Selection -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="booking_date" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt"></i> Date
                    </label>
                    <input type="date" class="form-control form-control-lg" id="booking_date" 
                           name="booking_date" required>
                </div>

                <!-- Time Selection -->
                <div class="col-md-6 mb-4">
                    <label for="booking_time" class="form-label fw-bold">
                        <i class="fas fa-clock"></i> Time
                    </label>
                    <select class="form-select form-select-lg" id="booking_time" name="booking_time" required>
                        <option value="">-- Choose time --</option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="12:00">12:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                        <option value="17:00">17:00</option>
                        <option value="18:00">18:00</option>
                        <option value="19:00">19:00</option>
                        <option value="20:00">20:00</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-danger btn-lg w-100">
                <i class="fas fa-check-circle"></i> Confirm Booking
            </button>
        </form>

        <!-- Booking Info -->
        <div class="alert alert-info mt-4" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-info-circle"></i> Informasi Penting
            </h5>
            <ul class="mb-0">
                <li>Booking online memerlukan konfirmasi dari barber</li>
                <li>Anda akan menerima notifikasi via email setelah konfirmasi</li>
                <li>Jika ingin cancel, hubungi kami minimal 1 jam sebelumnya</li>
                <li>Datang 5 menit lebih awal dari jadwal</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const branchSelect = document.getElementById('branch_id');
    const barberSelect = document.getElementById('barber_id');
    const oldBarberId  = "{{ old('barber_id') }}";

    branchSelect.addEventListener('change', function () {
        const branchId = this.value;
        barberSelect.innerHTML = '<option value="">-- Memuat barber... --</option>';
        barberSelect.disabled = true;

        if (!branchId) {
            barberSelect.innerHTML = '<option value="">-- Pilih cabang terlebih dahulu --</option>';
            return;
        }

        fetch(`/booking/barbers-by-branch/${branchId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(barbers => {
            if (barbers.length === 0) {
                barberSelect.innerHTML = '<option value="">Belum ada barber di cabang ini</option>';
            } else {
                barberSelect.innerHTML = '<option value="">-- Pilih barber --</option>';
                barbers.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.name;
                    if (String(b.id) === oldBarberId) opt.selected = true;
                    barberSelect.appendChild(opt);
                });
                barberSelect.disabled = false;
            }
        })
        .catch(() => {
            barberSelect.innerHTML = '<option value="">Gagal memuat barber</option>';
        });
    });

    // Restore branch/barber on validation error
    if (branchSelect.value) {
        branchSelect.dispatchEvent(new Event('change'));
    }
})();
</script>
@endsection
