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
        @if($activeBooking)
            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Booking Masih Berjalan
                    </h5>
                    @if($activeBooking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT)
                        <p class="mb-4">
                            Anda memiliki booking yang menunggu pembayaran DP. Silakan lanjutkan pembayaran di halaman My Booking.
                        </p>
                    @else
                        <p class="mb-4">
                            Anda masih memiliki booking yang sedang berjalan. Silakan tunggu hingga barber menyelesaikan booking Anda.
                        </p>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Service</div>
                            <div class="fw-semibold">{{ $activeBooking->service->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Barber</div>
                            <div class="fw-semibold">{{ $activeBooking->barber->user->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Cabang</div>
                            <div class="fw-semibold">{{ $activeBooking->barber->branch->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Tanggal & Jam</div>
                            <div class="fw-semibold">
                                {{ $activeBooking->booking_date->format('d M Y') }}
                                {{ \Carbon\Carbon::parse($activeBooking->booking_time)->format('H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Jumlah Orang</div>
                            <div class="fw-semibold">{{ $activeBooking->total_people ?? 1 }} orang</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Status</div>
                            <div>
                                @switch($activeBooking->status)
                                    @case(\App\Models\Booking::STATUS_WAITING_PAYMENT)
                                        <span class="badge bg-danger">Waiting Payment</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @break
                                    @case('confirmed')
                                        <span class="badge bg-info">Confirmed</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-primary">Processing</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($activeBooking->status) }}</span>
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('my-booking') }}" class="btn btn-outline-danger">
                        <i class="fas fa-receipt"></i>
                        {{ $activeBooking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT ? 'Lanjutkan Pembayaran' : 'Lihat My Booking' }}
                    </a>
                </div>
            </div>
        @else
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

            <!-- Service Selection -->
            <div class="mb-4">
                <label for="service_id" class="form-label fw-bold">
                    <i class="fas fa-cut"></i> Pilih Layanan
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
                    <i class="fas fa-user"></i> Pilih Barber
                </label>
                <select class="form-select form-select-lg" id="barber_id" name="barber_id" required disabled>
                    <option value="">-- Pilih cabang terlebih dahulu --</option>
                </select>
            </div>


            <!-- Date Selection -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <label for="booking_date" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt"></i> Tanggal
                    </label>
                    <input type="date" class="form-control form-control-lg" id="booking_date" 
                              name="booking_date" value="{{ old('booking_date') }}" min="{{ now('Asia/Jakarta')->toDateString() }}" required>
                </div>

                <!-- Time Selection -->
                <div class="col-md-4 mb-4">
                    <label for="booking_time" class="form-label fw-bold">
                        <i class="fas fa-clock"></i> Waktu
                    </label>
                    <select class="form-select form-select-lg" id="booking_time" name="booking_time" required disabled>
                        <option value="">-- Pilih barber dan tanggal dahulu --</option>
                    </select>
                </div>

                <!-- Total People -->
                <div class="col-md-4 mb-4">
                    <label for="total_people" class="form-label fw-bold">
                        <i class="fas fa-users"></i> Jumlah Orang
                    </label>
                    <input
                        type="number"
                        class="form-control form-control-lg"
                        id="total_people"
                        name="total_people"
                        min="1"
                        max="5"
                        value="{{ old('total_people', 1) }}"
                        required
                    >
                    <small class="text-muted">Minimal 1 orang, maksimal 5 orang dalam satu booking.</small>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-danger btn-lg w-100">
                <i class="fas fa-check-circle"></i> Confirm Booking
            </button>
        </form>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const showToast = (icon, title) => {
        if (!window.Swal) {
            return;
        }

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    };

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif

    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif

    @if($errors->any())
        showToast('error', @json($errors->first()));
    @endif
})();
</script>

@unless($activeBooking)
<script>
(function () {
    const branchSelect = document.getElementById('branch_id');
    const barberSelect = document.getElementById('barber_id');
    const bookingDateInput = document.getElementById('booking_date');
    const bookingTimeSelect = document.getElementById('booking_time');
    const availableTimesUrl = "{{ route('booking.availableTimes') }}";
    const barbersByBranchBaseUrl = @json(url('/booking/barbers-by-branch'));
    const oldBarberId  = "{{ old('barber_id') }}";
    const oldBookingTime = "{{ old('booking_time') }}";

    const showToast = (icon, title) => {
        if (!window.Swal) {
            return;
        }

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    };

    const resetTimeOptions = (label = '-- Pilih barber dan tanggal dahulu --') => {
        bookingTimeSelect.innerHTML = `<option value="">${label}</option>`;
        bookingTimeSelect.disabled = true;
    };

    const renderTimeOptions = (timeSlots, emptyMessage = 'Slot tidak tersedia') => {
        bookingTimeSelect.innerHTML = '<option value="">-- Pilih jam --</option>';

        if (!timeSlots.length) {
            bookingTimeSelect.innerHTML = `<option value="">${emptyMessage}</option>`;
            bookingTimeSelect.disabled = true;
            return;
        }

        timeSlots.forEach((time) => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            bookingTimeSelect.appendChild(option);
        });

        if (oldBookingTime && timeSlots.includes(oldBookingTime)) {
            bookingTimeSelect.value = oldBookingTime;
        }

        bookingTimeSelect.disabled = false;
    };

    const loadAvailableTimes = async () => {
        const barberId = barberSelect.value;
        const bookingDate = bookingDateInput.value;

        if (!barberId || !bookingDate) {
            resetTimeOptions();
            return;
        }

        bookingTimeSelect.innerHTML = '<option value="">-- Memuat slot... --</option>';
        bookingTimeSelect.disabled = true;

        try {
            const url = `${availableTimesUrl}?barber_id=${encodeURIComponent(barberId)}&booking_date=${encodeURIComponent(bookingDate)}`;
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('failed');
            }

            const payload = await response.json();
            const availableTimes = Array.isArray(payload.available_times) ? payload.available_times : [];
            const noSlotMessage = payload.message || 'Slot tidak tersedia';

            renderTimeOptions(availableTimes, noSlotMessage);
        } catch (error) {
            resetTimeOptions('-- Gagal memuat slot --');
            showToast('error', 'Gagal memuat slot jadwal');
        }
    };

    branchSelect.addEventListener('change', function () {
        const branchId = this.value;
        barberSelect.innerHTML = '<option value="">-- Memuat barber... --</option>';
        barberSelect.disabled = true;
        resetTimeOptions();

        if (!branchId) {
            barberSelect.innerHTML = '<option value="">-- Pilih cabang terlebih dahulu --</option>';
            return;
        }

        fetch(`${barbersByBranchBaseUrl}/${branchId}`, {
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

                if (barberSelect.value && bookingDateInput.value) {
                    loadAvailableTimes();
                }
            }
        })
        .catch(() => {
            barberSelect.innerHTML = '<option value="">Gagal memuat barber</option>';
        });
    });

    barberSelect.addEventListener('change', loadAvailableTimes);
    bookingDateInput.addEventListener('change', loadAvailableTimes);

    // Restore branch/barber on validation error
    if (branchSelect.value) {
        branchSelect.dispatchEvent(new Event('change'));
    } else {
        resetTimeOptions();
    }
})();
</script>
@endunless
@endsection
