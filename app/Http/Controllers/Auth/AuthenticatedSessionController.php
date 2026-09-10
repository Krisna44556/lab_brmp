<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirection berdasarkan Role User
        $user = Auth::user();

        if ($user->role === 'admin') {
           return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role === 'distributor') {
            return redirect()->intended(route('distributor.dashboard'));
        }

        // Jika Role 'user' (Pemohon), langsung arahkan ke Form Pengajuan atau Dashboard User
        return redirect()->intended(route('user.create_request'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Arahkan langsung ke halaman login setelah logout
        return redirect('/login');
    }
}
