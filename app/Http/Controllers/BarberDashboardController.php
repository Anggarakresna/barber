<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarberDashboardController extends Controller
{
    /**
     * Show the barber dashboard with their bookings.
     */
    public function index()
    {
        $barber = Auth::user()->barber;

        if (!$barber) {
            return redirect()->route('home')->with('error', 'Profil barber Anda belum terdaftar. Hubungi admin.');
        }

        $bookings = Booking::with(['user', 'service'])
            ->where('barber_id', $barber->id)
            ->orderByDesc('booking_date')
            ->orderBy('booking_time')
            ->get();

        $totalBookings    = $bookings->count();
        $completedBookings = $bookings->where('status', 'completed')->count();
        $pendingBookings  = $bookings->where('status', 'pending')->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();

        return view('dashboard.barber', compact(
            'barber',
            'bookings',
            'totalBookings',
            'completedBookings',
            'pendingBookings',
            'confirmedBookings'
        ));
    }

    /**
     * Update the status of a booking (accept / complete / cancel).
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $barber = Auth::user()->barber;

        // Barber can only update their own bookings
        if (!$barber || $booking->barber_id !== $barber->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$barber->is_active) {
            return redirect()->route('barber.dashboard')->with('error', 'Akun Anda sedang tidak aktif.');
        }

        $request->validate([
            'status' => ['required', 'in:confirmed,completed,cancelled'],
        ]);

        $booking->update(['status' => $request->status]);

        return redirect()->route('barber.dashboard')
            ->with('success', 'Status booking berhasil diperbarui.');
    }
}
