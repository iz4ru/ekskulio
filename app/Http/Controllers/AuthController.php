<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'login' => ['required', 'string'], // 1 field: email / username
            'password' => ['required', 'string'],
        ]);

        // 2. Tentukan apakah ini email atau username
        $loginValue = $request->input('login');
        $loginField = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 3. Ambil user dulu (untuk cek is_active & pesan error spesifik)
        $user = User::where($loginField, $loginValue)->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'login' => 'Akun tidak ditemukan.',
                ])
                ->onlyInput('login');
        }

        if (!$user->is_active) {
            return back()
                ->withErrors([
                    'login' => 'Akun Anda tidak aktif. Silakan hubungi pihak kesiswaan atau admin.',
                ])
                ->onlyInput('login');
        }

        // 4. Attempt login dengan field dinamis
        $credentials = [
            $loginField => $loginValue,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return match ($user->role) {
                'kesiswaan' => redirect()
                    ->route('kesiswaan.dashboard')
                    ->with('success', 'Login berhasil, selamat datang kembali ' . Auth::user()->name . ' !'),
                'pembina' => redirect()
                    ->route('pembina.dashboard')
                    ->with('success', 'Login berhasil, selamat datang kembali ' . Auth::user()->name . ' !'),
                'admin' => redirect()
                    ->route('admin.dashboard')
                    ->with('success', 'Login berhasil, selamat datang kembali ' . Auth::user()->name . ' !'),
                default => redirect()
                    ->route('login')
                    ->withErrors(['login' => 'Role pengguna tidak dikenali.']),
            };
        }

        return back()
            ->withErrors([
                'password' => 'Password yang Anda masukkan tidak sesuai.',
            ])
            ->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil, sampai jumpa lagi!');
    }
}
