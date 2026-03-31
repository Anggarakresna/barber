<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarberController extends Controller
{
    public function index()
    {
        $barbers = Barber::with(['user', 'branch'])->latest()->paginate(10);
        return view('admin.barbers.index', compact('barbers'));
    }

    public function create()
    {
        $users = User::where('role', 'barber')
            ->whereDoesntHave('barber')
            ->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.barbers.create', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => ['required', 'exists:users,id', 'unique:barbers,user_id'],
            'branch_id'  => ['nullable', 'exists:branches,id'],
            'phone'      => ['required', 'string', 'max:20'],
            'experience' => ['required', 'integer', 'min:0'],
            'bio'        => ['nullable', 'string'],
            'photo'      => ['nullable', 'file', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $ext = strtolower($request->file('photo')->getClientOriginalExtension());
            if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
                return back()->withErrors(['photo' => 'Photo harus berupa file gambar (jpeg, jpg, png, gif).'])->withInput();
            }
            $validated['photo'] = $request->file('photo')->store('barbers', 'public');
        }

        Barber::create($validated);

        return redirect()->route('admin.barbers.index')->with('success', 'Barber berhasil ditambahkan!');
    }

    public function edit(Barber $barber)
    {
        $barber->load('user');
        $users = User::where('role', 'barber')
            ->where(function ($query) use ($barber) {
                $query->whereDoesntHave('barber')
                    ->orWhere('id', $barber->user_id);
            })
            ->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.barbers.edit', compact('barber', 'users', 'branches'));
    }

    public function update(Request $request, Barber $barber)
    {
        $validated = $request->validate([
            'user_id'    => ['required', 'exists:users,id', 'unique:barbers,user_id,' . $barber->id],
            'branch_id'  => ['nullable', 'exists:branches,id'],
            'phone'      => ['required', 'string', 'max:20'],
            'experience' => ['required', 'integer', 'min:0'],
            'bio'        => ['nullable', 'string'],
            'photo'      => ['nullable', 'file', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $ext = strtolower($request->file('photo')->getClientOriginalExtension());
            if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
                return back()->withErrors(['photo' => 'Photo harus berupa file gambar (jpeg, jpg, png, gif).'])->withInput();
            }
            if ($barber->photo) {
                Storage::disk('public')->delete($barber->photo);
            }
            $validated['photo'] = $request->file('photo')->store('barbers', 'public');
        }

        $barber->update($validated);

        return redirect()->route('admin.barbers.index')->with('success', 'Barber berhasil diupdate!');
    }

    public function destroy(Barber $barber)
    {
        if ($barber->photo) {
            Storage::disk('public')->delete($barber->photo);
        }

        $barber->delete();

        return redirect()->route('admin.barbers.index')->with('success', 'Barber berhasil dihapus!');
    }

    public function toggleStatus(Barber $barber)
    {
        $barber->update(['is_active' => !$barber->is_active]);

        return redirect()->route('admin.barbers.index')->with('success', 'Status barber berhasil diubah.');
    }
}
