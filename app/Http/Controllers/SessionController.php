<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{

    /**
     * Menampilkan halaman form login.
     * @return \Illuminate\View\View
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Mengautentikasi pengguna berdasarkan email dan password.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authentication(Request $request)
    {
        // Validasi input
        $attributes = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        // Debugging kredensial
        \Log::info('Attempting login for username: ' . $attributes['username']);

        if (Auth::attempt(['username' => $attributes['username'], 'password' => $attributes['password']], $remember)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Berhasil Login');
        } else {
            \Log::error('Login failed for username: ' . $attributes['username']);
            // Pastikan hanya mengirim pesan ini sekali
            return redirect()->route('login')->withErrors(['login_error' => 'Username atau password salah']);
        }
    }

    /**
     * Mengeluarkan pengguna dari sesi dan mengalihkan mereka ke halaman login.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Logout pengguna
        Auth::logout();

        // Hapus sesi
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login dengan pesan logout
        return redirect()->route('login')->with('logout', 'Berhasil Keluar');
    }
}
