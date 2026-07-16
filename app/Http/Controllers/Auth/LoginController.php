<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('These credentials do not match our records.'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Redirect unapproved users to pending approval
        if (! $user->isSuperAdmin() && ! $user->isApproved()) {
            return redirect()->route('pending-approval');
        }

        return redirect()->intended(route('app.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function pendingApproval(Request $request)
    {
        $user = $request->user();

        // If user is approved, redirect to dashboard
        if ($user->isSuperAdmin() || $user->isApproved()) {
            return redirect()->route('app.dashboard');
        }

        return Inertia::render('Auth/PendingApproval');
    }
}
