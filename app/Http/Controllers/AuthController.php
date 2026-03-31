<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show register form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Create user with customer role by default
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',  // Automatically set as customer
        ]);

        // Login user
        Auth::login($user);

        return redirect()->intended(route('home'))->with('success', 'Registration successful! Welcome to BarberShop!');
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard')->with('success', 'Login successful!'),
                'barber' => redirect()->route('barber.dashboard')->with('success', 'Login successful!'),
                'customer' => redirect()->intended(route('home'))->with('success', 'Login successful!'),
                default => redirect()->intended(route('home'))->with('success', 'Login successful!'),
            };
        }

        // Failed authentication
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logout successful!');
    }

    /**
     * Show dashboard based on user role
     * Note: Only admin and barber have dashboards. Customers are redirected to home.
     */
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'customer') {
            return redirect()->route('home');
        }

        return match ($user->role) {
            'admin' => view('dashboard.admin', ['user' => $user]),
            'barber' => view('dashboard.barber', ['user' => $user]),
            default => redirect()->route('home'),
        };
    }
}
