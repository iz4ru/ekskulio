<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'kesiswaan' => $this->kesiswaan(),
            'pembina'   => $this->pembina(),
            'admin'     => $this->admin(),
            default     => abort(403, 'Unauthorized action.'),
        };
    }

    protected function kesiswaan()
    {
        // ambil data rekap global ekskul, siswa, absensi, nilai, dsb
        $data = [
            // contoh:
            // 'totalEkskul' => Extracurricular::count(),
            // 'totalPembina' => User::where('role', 'pembina')->count(),
            // dst...
        ];

        return view('role.kesiswaan.contents.dashboard', $data);
    }

    protected function pembina()
    {
        // ambil data khusus pembina berdasarkan ekskul yang dia pegang
        $user = Auth::user();

        $data = [
            // contoh:
            // 'myEkskul' => $user->extracurriculars,
            // 'todaySessions' => ...
        ];

        return view('role.pembina.contents.dashboard', $data);
    }

    protected function admin()
    {
        // kalau ada role admin teknis (opsional)
        $data = [
            // data untuk admin sistem
        ];

        return view('role.admin.contents.dashboard', $data);
    }
}
