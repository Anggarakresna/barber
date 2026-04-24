@extends('layouts.app')

@section('title', 'My Booking')

@section('content')
<div class="row mb-4">
    <div class="col-lg-8">
        <h1 class="fw-bold mb-3">My Booking</h1>
        <p class="lead text-muted">Daftar riwayat booking Anda di BarberShop.</p>
    </div>
</div>

@if($paymentExpiredMessage)
    <div class="alert alert-warning border-0 shadow-sm">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $paymentExpiredMessage }}
    </div>
@endif

@if($waitingPaymentBooking && $waitingPaymentBooking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT && $waitingPaymentBooking->payment_status === \App\Models\Booking::PAYMENT_STATUS_UNPAID)
    @php
        $paymentExpiredAt = $waitingPaymentBooking->payment_expired_at ?? $waitingPaymentBooking->payment_deadline;
    @endphp
    <div class="card border-0 shadow-lg mb-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0891b2 100%);">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-4 text-center">
                    <div class="bg-white rounded-4 p-3 shadow-sm d-inline-block">
                        <img
                            src="{{ asset('images/qris-dp.svg') }}"
                            alt="QRIS Pembayaran DP"
                            class="img-fluid rounded-3"
                            style="max-width: 220px;"
                        >
                    </div>
                    <p class="small text-white-50 mt-3 mb-0">Bayar DP via Midtrans: QRIS, GoPay, ShopeePay, VA, dan metode lainnya.</p>
                </div>
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                        <span class="badge bg-light text-dark">DP Midtrans</span>
                    </div>

                    <h3 class="fw-bold mb-3">Selesaikan Pembayaran DP Anda</h3>

                    <div class="bg-white bg-opacity-10 rounded-4 p-3 p-md-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small text-white-50">Nominal DP</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($waitingPaymentBooking->dp_amount ?? 20000, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-white-50">Layanan</div>
                                <div class="fw-semibold">{{ $waitingPaymentBooking->service->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-white-50">Barber</div>
                                <div class="fw-semibold">{{ $waitingPaymentBooking->barber->user->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-white-50">Tanggal & Jam Booking</div>
                                <div class="fw-semibold">
                                    {{ $waitingPaymentBooking->booking_date->format('d M Y') }}
                                    {{ \Carbon\Carbon::parse($waitingPaymentBooking->booking_time)->format('H:i') }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="small text-white-50">Batas Pembayaran</div>
                                <div class="fw-semibold">
                                    {{ $paymentExpiredAt ? $paymentExpiredAt->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }} WIB
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <div class="small text-white-50">Sisa waktu pembayaran</div>
                            <div
                                id="payment-countdown"
                                class="fw-bold fs-2 lh-1"
                                data-payment-deadline="{{ optional($paymentExpiredAt)->toIso8601String() }}"
                            >
                                30:00
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button
                                type="button"
                                id="pay-dp-button"
                                class="btn btn-warning btn-lg px-4 fw-semibold"
                                data-snap-token="{{ $waitingPaymentBooking->midtrans_snap_token }}"
                            >
                                <i class="fas fa-wallet me-2"></i>Bayar DP Sekarang
                            </button>

                            <form action="{{ route('booking.paymentSync', $waitingPaymentBooking) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-light btn-lg px-4 fw-semibold text-dark">
                                    <i class="fas fa-rotate me-2"></i>Cek Status
                                </button>
                            </form>
                        </div>
                    </div>

                    <form id="sync-payment-form" action="{{ route('booking.paymentSync', $waitingPaymentBooking) }}" method="POST" class="d-none">
                            @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@if($bookings->total() === 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-4 fw-bold">Anda belum melakukan booking.</h4>
            <p class="text-muted">Silakan lakukan booking terlebih dahulu.</p>
            <a href="{{ route('booking') }}" class="btn btn-danger mt-2">
                <i class="fas fa-calendar-plus"></i> Booking Sekarang
            </a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Barber</th>
                            <th>Booking Date</th>
                            <th>Booking Time</th>
                            <th>Jumlah Orang</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>{{ ($bookings->currentPage() - 1) * $bookings->perPage() + $loop->iteration }}</td>
                                <td>{{ $booking->service->name ?? '-' }}</td>
                                <td>{{ $booking->barber->user->name ?? '-' }}</td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                                <td>{{ $booking->total_people ?? 1 }}</td>
                                <td>
                                    @switch($booking->status)
                                        @case(\App\Models\Booking::STATUS_WAITING_PAYMENT)
                                            <span class="badge bg-danger">Waiting Payment</span>
                                            @break
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
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($booking->status) }}</span>
                                    @endswitch

                                    @if($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_PAID)
                                        <span class="badge bg-primary ms-1">Paid</span>
                                    @elseif($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_UNPAID && $booking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT)
                                        <span class="badge bg-light text-dark ms-1">Unpaid</span>
                                    @elseif(in_array($booking->payment_status, [\App\Models\Booking::PAYMENT_STATUS_EXPIRED, \App\Models\Booking::PAYMENT_STATUS_CANCELLED], true))
                                        <span class="badge bg-secondary ms-1">{{ ucfirst($booking->payment_status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT && $booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_UNPAID)
                                        <span class="text-muted small">Gunakan panel pembayaran</span>
                                    @elseif(in_array($booking->status, ['pending', 'confirmed']))
                                        <form action="{{ route('booking.cancel', $booking) }}" method="POST"
                                              onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($bookings->hasPages())
            <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
                {{ $bookings->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(config('services.midtrans.client_key'))
    <script
        src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('services.midtrans.client_key') }}"
    ></script>
@endif
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

    const countdownEl = document.getElementById('payment-countdown');
    const payDpButton = document.getElementById('pay-dp-button');
    const syncPaymentForm = document.getElementById('sync-payment-form');
    const syncPaymentJsonUrl = @json($waitingPaymentBooking ? route('booking.paymentSyncJson', $waitingPaymentBooking) : null);
    const completePaymentUrl = @json($waitingPaymentBooking ? route('booking.paymentComplete', $waitingPaymentBooking) : null);
    const myBookingsUrl = @json(route('my-booking'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const syncPaymentStatus = () => {
        if (!syncPaymentForm) {
            return;
        }

        syncPaymentForm.submit();
    };

    const syncPaymentStatusJson = async () => {
        if (!syncPaymentJsonUrl || !csrfToken) {
            return null;
        }

        const response = await fetch(syncPaymentJsonUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            throw new Error('sync-failed');
        }

        return response.json();
    };

    const completePaymentFromSnap = async (payload) => {
        if (!completePaymentUrl || !csrfToken) {
            return null;
        }

        const response = await fetch(completePaymentUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload || {}),
        });

        if (!response.ok) {
            throw new Error('complete-failed');
        }

        return response.json();
    };

    const redirectToMyBookings = () => {
        window.location.href = myBookingsUrl || '/my-bookings';
    };

    if (payDpButton) {
        payDpButton.addEventListener('click', () => {
            const snapToken = payDpButton.dataset.snapToken;

            if (!snapToken) {
                showToast('error', 'Snap token Midtrans belum tersedia. Silakan klik Cek Status atau refresh halaman.');
                return;
            }

            if (!window.snap || typeof window.snap.pay !== 'function') {
                showToast('error', 'Midtrans Snap belum termuat. Periksa MIDTRANS_CLIENT_KEY Anda.');
                return;
            }

            window.snap.pay(snapToken, {
                onSuccess: async function (result) {
                    payDpButton.disabled = true;
                    payDpButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memverifikasi...';

                    try {
                        const completion = await completePaymentFromSnap(result);

                        if (completion && completion.state === 'paid') {
                            redirectToMyBookings();
                            return;
                        }
                    } catch (error) {
                        // Fallback to payment sync below.
                    }

                    try {
                        const syncResult = await syncPaymentStatusJson();

                        if (syncResult && (syncResult.state === 'paid' || syncResult.state === 'updated')) {
                            redirectToMyBookings();
                            return;
                        }
                    } catch (error) {
                        // Continue to redirect so customer returns to My Booking page.
                    }

                    redirectToMyBookings();
                },
                onPending: async function () {
                    try {
                        await syncPaymentStatusJson();
                    } catch (error) {
                        // Keep pending flow on page even if sync temporarily fails.
                    }

                    showToast('info', 'Pembayaran masih pending. Silakan selesaikan pembayaran sebelum batas waktu habis.');
                },
                onError: async function () {
                    try {
                        const result = await syncPaymentStatusJson();

                        if (result && result.state === 'cancelled') {
                            redirectToMyBookings();
                            return;
                        }
                    } catch (error) {
                        // Keep current page and show error toast below.
                    }

                    showToast('error', 'Pembayaran gagal diproses. Silakan coba lagi atau cek status pembayaran.');
                },
                onClose: function () {
                    showToast('info', 'Popup pembayaran ditutup. Anda bisa lanjutkan kapan saja sebelum waktu habis.');
                }
            });
        });
    }

    if (!countdownEl) {
        return;
    }

    const paymentDeadlineRaw = countdownEl.dataset.paymentDeadline;
    const paymentDeadline = paymentDeadlineRaw ? new Date(paymentDeadlineRaw).getTime() : NaN;

    if (Number.isNaN(paymentDeadline)) {
        countdownEl.textContent = '--:--';
        return;
    }

    const formatRemainingTime = (milliseconds) => {
        const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
        const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');

        return minutes + ':' + seconds;
    };

    const updateCountdown = () => {
        const now = Date.now();
        const distance = paymentDeadline - now;

        if (distance <= 0) {
            countdownEl.textContent = '00:00';
            window.setTimeout(() => {
                window.location.reload();
            }, 1200);
            return false;
        }

        countdownEl.textContent = formatRemainingTime(distance);
        return true;
    };

    if (!updateCountdown()) {
        return;
    }

    const countdownTimer = window.setInterval(() => {
        const shouldContinue = updateCountdown();

        if (!shouldContinue) {
            window.clearInterval(countdownTimer);
        }
    }, 1000);
})();
</script>
@endsection
