<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Show the booking form.
     */
    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $services = Service::all();

        return view('booking', compact('branches', 'services'));
    }

    /**
     * Return barbers belonging to a given branch (JSON, for AJAX).
     */
    public function barbersByBranch(Branch $branch)
    {
        $barbers = $branch->barbers()->where('is_active', true)->with('user')->get()->map(fn($b) => [
            'id'   => $b->id,
            'name' => $b->user->name,
        ]);

        return response()->json($barbers);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barber_id' => ['required', 'exists:barbers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required'],
        ]);

        $barber = Barber::find($validated['barber_id']);
        if (!$barber->is_active) {
            return back()->withErrors(['barber_id' => 'Barber yang dipilih sedang tidak aktif.'])->withInput();
        }

        Booking::create([
            'user_id' => Auth::id(),
            'barber_id' => $validated['barber_id'],
            'service_id' => $validated['service_id'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'status' => 'pending',
        ]);

        return redirect()->route('my-booking')->with('success', 'Booking berhasil dibuat!');
    }

    /**
     * Cancel a booking owned by the currently logged-in customer.
     */
    public function cancel(Booking $booking)
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Only pending or confirmed bookings can be cancelled
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->route('my-booking')
                ->with('error', 'Booking tidak dapat dibatalkan karena statusnya sudah ' . $booking->status . '.');
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('my-booking')
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    /**
     * Display bookings for the currently logged-in customer.
     */
    public function myBooking()
    {
        $bookings = Booking::with(['service', 'barber.user'])
            ->where('user_id', Auth::id())
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time')
            ->paginate(5);

        return view('my-booking', compact('bookings'));
    }
}
