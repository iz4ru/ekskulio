<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginValue = $request->input('login');
        $loginField = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

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

    public function sendResetPassword()
    {
        return view('auth.send-reset-password');
    }

    public function sendResetPasswordLink(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|exists:users,email',
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.exists' => 'Email tidak terdaftar di sistem.',
            ],
        );

        $status = Password::sendResetLink($request->only('email'));

        $throttleMinutes = config('auth.passwords.users.throttle', 15);

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('reset-password.send-link.success')
                ->with([
                    'success' => 'Link reset password telah dikirim ke email Anda.',
                    'retry_after' => $throttleMinutes,
                ]);
        }

        if ($status === Password::RESET_THROTTLED) {
            return redirect()
                ->route('reset-password.send-link.index')
                ->with([
                    'retry_after' => $throttleMinutes,
                ])
                ->withErrors([
                    'error' => "Silakan tunggu {$throttleMinutes} menit sebelum meminta lagi.",
                ]);
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }

    public function sendResetPasswordSuccess()
    {
        return view('auth.send-reset-password-success');
    }

    public function showResetPasswordForm(Request $request)
    {
        if (!$request->filled('token') || !$request->filled('email')) {
            return redirect()
                ->route('reset-password.send-link.index')
                ->withErrors(['error' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.']);
        }

        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate(
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ],
            [
                'token.required' => 'Token reset password tidak ditemukan.',
                'email.required' => 'Alamat email wajib diisi.',
                'email.email' => 'Format alamat email tidak valid.',
                'password.required' => 'Password baru wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'password.min' => 'Password minimal harus 8 karakter.',
            ],
        );

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, string $password) {
            DB::transaction(function () use ($user, $password) {
                $user
                    ->forceFill([
                        'password' => Hash::make($password),
                    ])
                    ->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                Log::create([
                    'user_id' => $user->id,
                    'activity' => 'Reset password',
                    'detail' => $user->name . ' berhasil mereset password akun melalui link email.',
                ]);
            });
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password Anda berhasil direset! Silakan login dengan password baru.');
        }

        $errorMessage = match ($status) {
            Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kedaluwarsa.',
            Password::INVALID_USER => 'Tidak ditemukan akun dengan alamat email tersebut.',
            Password::RESET_THROTTLED => 'Terlalu banyak percobaan. Silakan tunggu beberapa menit sebelum mencoba lagi.',
            default => 'Terjadi kesalahan saat mereset password. Silakan coba lagi atau minta link reset yang baru.',
        };

        return back()
            ->withErrors(['email' => $errorMessage])
            ->withInput($request->only('email'));
    }
}
