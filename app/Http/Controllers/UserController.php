<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Extracurricular;
use App\Models\ExtracurricularUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $x['users'] = User::where('role', 'admin')->orWhere('role', 'pembina')->orderBy('role', 'desc')->get();

        return view('role.kesiswaan.contents.user-management.index', $x);
    }

    public function create()
    {
        $x['extracurriculars'] = Extracurricular::orderBy('name')->get();

        return view('role.kesiswaan.contents.user-management.create', $x);
    }

    public function generateUsername(Request $request)
    {
        $name = $request->name;

        // Logic generate username (contoh: ambil 3 huruf pertama + angka unik)
        $cleanName = preg_replace('/[^a-zA-Z\s]/', '', $name);
        $words = explode(' ', trim($cleanName));
        $username = strtolower(substr(implode('', array_slice($words, 0, 2)), 0));

        // Tambah angka jika sudah ada
        $counter = 1;
        while (User::where('username', $username . ($counter > 1 ? $counter : ''))->exists()) {
            $counter++;
        }

        return response()->json(['username' => $username . ($counter > 1 ? $counter : '')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:20',
                'role' => 'required|in:admin,pembina',
                'extracurricular_id' => 'nullable|exists:extracurriculars,id',
                'password' => 'required|min:8|confirmed',
            ],
            [
                'role.in' => 'Role harus Admin atau Pembina',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ],
        );

        $user = User::create([
            'name' => ucwords(strtoupper($validated['name'])),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        // ✅ Relasi ExtracurricularUser jika ada
        if ($request->extracurricular_id) {
            ExtracurricularUser::create([
                'extracurricular_id' => $request->extracurricular_id,
                'user_id' => $user->id,
            ]);
        }

        return redirect()
            ->route('user-management.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan!");
    }

    public function edit($uuid)
    {
        $x['user'] = User::with('extracurriculars.extracurricular')->where('uuid', $uuid)->firstOrFail();
        $x['extracurriculars'] = Extracurricular::orderBy('name')->get();

        return view('role.kesiswaan.contents.user-management.edit', $x);
    }

    public function update(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,pembina',
            'extracurricular_id' => 'nullable|exists:extracurriculars,id',
            'password' => 'nullable|min:8|confirmed',
        ], [
            'username.unique' => 'Username sudah digunakan pengguna lain',
            'email.unique' => 'Email sudah digunakan pengguna lain',
            'role.in' => 'Role harus Admin atau Pembina',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        // Update user data
        $user->update([
            'name' => ucwords(strtoupper($validated['name'])),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
        ]);

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update relasi ekstrakurikuler
        $user->extracurriculars()->delete(); // Hapus relasi lama

        if ($request->extracurricular_id) {
            ExtracurricularUser::create([
                'extracurricular_id' => $request->extracurricular_id,
                'user_id' => $user->id,
            ]);
        }

        return redirect()
            ->route('user-management.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui!");
    }

    public function destroy(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        // Validasi password
        $request->validate(['password' => 'required'], [
            'password.required' => 'Password wajib diisi untuk menghapus pengguna.',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah!']);
        }

        // ✅ Pakai relasi extracurricularList (lebih clean)
        $extracurriculars = $user->extracurricularList;

        if ($extracurriculars->count() > 0) {
            $names = $extracurriculars->pluck('name')->implode(', ');
            
            return back()->withErrors([
                'extracurricular' => "Pengguna tidak dapat dihapus karena masih mengampu ekstrakurikuler: {$names}. Hapus atau ganti pembina terlebih dahulu.",
            ]);
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('user-management.index')
            ->with('success', "Pengguna {$name} berhasil dihapus!");
    }

    public function toggleActive(User $user, Request $request)
    {
        // Validasi password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('user-management.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        // Toggle status
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()
            ->route('user-management.index')
            ->with('success', "Akun {$user->name} berhasil {$status}!");
    }
}
