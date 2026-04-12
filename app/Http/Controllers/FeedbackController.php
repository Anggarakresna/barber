<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Show feedback form for guest and customer.
     */
    public function create()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && !$user->isCustomer()) {
            abort(403, 'Halaman feedback hanya untuk customer atau guest.');
        }

        return view('feedback.create');
    }

    /**
     * Store incoming feedback from guest or customer.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && !$user->isCustomer()) {
            abort(403, 'Halaman feedback hanya untuk customer atau guest.');
        }

        $rules = [
            'message' => ['required', 'string', 'min:5'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ];

        if (!$user) {
            $rules['name'] = ['required', 'string', 'max:255'];
        } else {
            $rules['name'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        Feedback::create([
            'user_id' => Auth::id(),
            'name' => $user ? $user->name : ($validated['name'] ?? null),
            'message' => $validated['message'],
            'rating' => $validated['rating'] ?? null,
        ]);

        return redirect()->route('feedback.create')
            ->with('success', 'Terima kasih! Kritik dan saran berhasil dikirim.');
    }
}
