<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    /**
     * Display all feedback entries.
     */
    public function index()
    {
        $feedbacks = Feedback::with('user')->latest('created_at')->paginate(5);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    /**
     * Mark a feedback entry as read.
     */
    public function markAsRead(Feedback $feedback)
    {
        $feedback->update(['is_read' => true]);

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback ditandai sebagai sudah dibaca.');
    }

    /**
     * Delete a feedback entry.
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback berhasil dihapus.');
    }
}
