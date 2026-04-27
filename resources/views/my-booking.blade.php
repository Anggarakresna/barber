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
    <div id="dp-payment-panel" class="card border-0 shadow-lg mb-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0891b2 100%);">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-5">
                    <div class="bg-white rounded-4 p-3 shadow-sm h-100 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-dark">QRIS Diprioritaskan</span>
                            <span class="small text-muted">Midtrans Snap</span>
                        </div>

                        <div
                            id="snap-embed-container"
                            class="border rounded-3 overflow-hidden bg-white flex-grow-1"
                            style="min-height: 420px;"
                        ></div>

                        <div id="snap-embed-fallback" class="small text-muted mt-3 d-none">
                            Mode embed belum tersedia di browser ini. Gunakan tombol popup pembayaran.
                        </div>
                    </div>
                    <p class="small text-white-50 mt-3 mb-0">Silakan scan QRIS untuk bayar DP. Metode lain tetap tersedia dari Midtrans.</p>
                </div>
                <div class="col-lg-7">
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
                            <div class="small text-white-50 mt-2">Status: Menunggu Pembayaran</div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button
                                type="button"
                                id="pay-dp-button"
                                class="btn btn-warning btn-lg px-4 fw-semibold d-none"
                                data-snap-token="{{ $waitingPaymentBooking->midtrans_snap_token }}"
                            >
                                <i class="fas fa-wallet me-2"></i>Buka Popup Pembayaran
                            </button>

                            <form action="{{ route('booking.paymentSync', $waitingPaymentBooking) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-light btn-lg px-4 fw-semibold text-dark">
                                    <i class="fas fa-rotate me-2"></i>Cek Status
                                </button>
                            </form>
                        </div>
                    </div>
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
                            @php
                                $bookingDateTime = \Carbon\Carbon::parse(
                                    $booking->booking_date->format('Y-m-d') . ' ' . \Carbon\Carbon::parse($booking->booking_time)->format('H:i:s'),
                                    'Asia/Jakarta'
                                );

                                $isMidtransFailed = $booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_CANCELLED
                                    && in_array((string) $booking->midtrans_transaction_status, ['cancel', 'deny', 'failure'], true);

                                $canRetryPayment = ($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_EXPIRED || $isMidtransFailed)
                                    && in_array($booking->status, [\App\Models\Booking::STATUS_CANCELLED, \App\Models\Booking::STATUS_WAITING_PAYMENT], true)
                                    && $bookingDateTime->isFuture();
                            @endphp
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
                                            <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
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
                                        <span class="badge bg-primary ms-1">DP Sudah Dibayar</span>
                                    @elseif($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_UNPAID && $booking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT)
                                        <span class="badge bg-light text-dark ms-1">DP Belum Dibayar</span>
                                    @elseif($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_EXPIRED)
                                        <span class="badge bg-secondary ms-1">DP Expired</span>
                                    @elseif($booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_CANCELLED)
                                        <span class="badge bg-secondary ms-1">DP Gagal</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status === \App\Models\Booking::STATUS_WAITING_PAYMENT && $booking->payment_status === \App\Models\Booking::PAYMENT_STATUS_UNPAID)
                                        <span class="text-muted small">Panel pembayaran aktif</span>
                                    @elseif($canRetryPayment)
                                        <form action="{{ route('booking.paymentRetry', $booking) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning text-dark">
                                                <i class="fas fa-rotate me-1"></i> Bayar Ulang
                                            </button>
                                        </form>
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
@if(config('midtrans.clientKey') || config('midtrans.client_key') || config('services.midtrans.client_key'))
    <script
        src="{{ config('midtrans.isProduction', config('midtrans.is_production', config('services.midtrans.is_production'))) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.clientKey', config('midtrans.client_key', config('services.midtrans.client_key'))) }}"
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
    const paymentPanel = document.getElementById('dp-payment-panel');
    const snapEmbedContainer = document.getElementById('snap-embed-container');
    const snapEmbedFallback = document.getElementById('snap-embed-fallback');
    const payDpButton = document.getElementById('pay-dp-button');
    const syncPaymentJsonUrl = @json($waitingPaymentBooking ? route('booking.paymentSyncJson', $waitingPaymentBooking) : null);
    const completePaymentUrl = @json($waitingPaymentBooking ? route('booking.paymentComplete', $waitingPaymentBooking) : null);
    const myBookingsUrl = @json(route('my-booking'));
    const preferredPaymentType = @json(config('midtrans.preferred_payment_type', 'qris'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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

    const handleResolvedPaymentState = (result) => {
        if (!result || !result.state) {
            return false;
        }

        if (result.state === 'paid' || result.state === 'updated' || result.state === 'cancelled') {
            redirectToMyBookings();
            return true;
        }

        return false;
    };

    const verifyPaymentState = async (payload = null) => {
        if (payload) {
            try {
                const completion = await completePaymentFromSnap(payload);

                if (handleResolvedPaymentState(completion)) {
                    return;
                }
            } catch (error) {
                // Continue with direct sync fallback below.
            }
        }

        try {
            const syncResult = await syncPaymentStatusJson();

            if (handleResolvedPaymentState(syncResult)) {
                return;
            }

            if (syncResult && syncResult.state === 'pending') {
                showToast('info', 'Status DP: Menunggu Pembayaran.');
            }
        } catch (error) {
            // Silent fallback: webhook will still update payment state.
        }
    };

    const buildSnapCallbacks = () => ({
        selectedPaymentType: preferredPaymentType || 'qris',
        onSuccess: async function (result) {
            if (payDpButton) {
                payDpButton.disabled = true;
                payDpButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memverifikasi...';
            }

            await verifyPaymentState(result);
            redirectToMyBookings();
        },
        onPending: async function (result) {
            await verifyPaymentState(result);
            showToast('info', 'Pembayaran masih pending. Silakan selesaikan pembayaran sebelum batas waktu habis.');
        },
        onError: async function () {
            await verifyPaymentState();
            showToast('error', 'Pembayaran gagal diproses. Silakan coba lagi atau klik bayar ulang saat status expired.');
        },
        onClose: function () {
            showToast('info', 'Panel pembayaran ditutup. Anda bisa melanjutkan pembayaran sebelum batas waktu habis.');
        }
    });

    const openSnapPayment = () => {
        if (!payDpButton) {
            return;
        }

        const snapToken = payDpButton.dataset.snapToken;

        if (!snapToken) {
            showToast('error', 'Snap token Midtrans belum tersedia. Silakan klik Cek Status atau refresh halaman.');
            return;
        }

        if (!window.snap || typeof window.snap.pay !== 'function') {
            showToast('error', 'Midtrans Snap belum termuat. Periksa MIDTRANS_CLIENT_KEY Anda.');
            return;
        }

        window.snap.pay(snapToken, buildSnapCallbacks());
    };

    const showPopupFallback = () => {
        if (payDpButton) {
            payDpButton.classList.remove('d-none');
        }

        if (snapEmbedFallback) {
            snapEmbedFallback.classList.remove('d-none');
        }
    };

    const mountSnapEmbed = () => {
        if (!payDpButton || !snapEmbedContainer) {
            return false;
        }

        const snapToken = payDpButton.dataset.snapToken;

        if (!snapToken || !window.snap || typeof window.snap.embed !== 'function') {
            return false;
        }

        snapEmbedContainer.innerHTML = '';
        window.snap.embed(snapToken, Object.assign(buildSnapCallbacks(), {
            embedId: 'snap-embed-container',
        }));

        return true;
    };

    if (payDpButton) {
        payDpButton.addEventListener('click', openSnapPayment);
    }

    if (paymentPanel) {
        const embedded = mountSnapEmbed();

        if (!embedded) {
            showPopupFallback();
        }

        if (syncPaymentJsonUrl) {
            window.setInterval(async () => {
                try {
                    const syncResult = await syncPaymentStatusJson();
                    handleResolvedPaymentState(syncResult);
                } catch (error) {
                    // Keep polling in the background while waiting for webhook updates.
                }
            }, 12000);
        }
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
