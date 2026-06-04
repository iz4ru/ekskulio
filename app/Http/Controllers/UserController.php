<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Log;
use App\Models\Extracurricular;
use App\Models\ExtracurricularUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['admin', 'pembina']);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        });

        $users = $query->orderBy('role', 'desc')->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('role.kesiswaan.contents.user-management.index', compact('users'));
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
        $user = Auth::user();
        
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:20',
                'role' => 'required|in:admin,pembina',
                'extracurricular_ids' => 'nullable|array',
                'extracurricular_ids.*' => 'exists:extracurriculars,id',
                'password' => 'required|min:8|confirmed',
            ],
            [
                'name.required' => 'Nama wajib diisi',
                'username.required' => 'Username wajib diisi',
                'username.unique' => 'Username sudah digunakan',
                'email.required' => 'Email wajib diisi',
                'email.unique' => 'Email sudah digunakan',
                'phone.required' => 'Nomor telepon wajib diisi',
                'role.required' => 'Role wajib dipilih',
                'role.in' => 'Role harus Admin atau Pembina',
                'extracurricular_ids.array' => 'Format ekstrakurikuler tidak valid',
                'extracurricular_ids.*.exists' => 'Salah satu ekstrakurikuler yang dipilih tidak ditemukan',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]
        );

        // Cek jika Admin memilih ekskul
        if ($validated['role'] === 'admin' && $request->filled('extracurricular_ids')) {
            return back()->withErrors(['extracurricular_ids' => 'Admin tidak boleh mengampu ekstrakurikuler'])->withInput();
        }

        DB::transaction(function () use ($validated, $user, $request) {
            $newUser = User::create([
                'name' => ucwords(strtolower($validated['name'])),
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // Looping untuk menyimpan multiple ekskul
            $ekskulNames = [];
            if ($validated['role'] === 'pembina' && $request->filled('extracurricular_ids')) {
                foreach ($request->extracurricular_ids as $ekskulId) {
                    ExtracurricularUser::create([
                        'extracurricular_id' => $ekskulId,
                        'user_id' => $newUser->id,
                    ]);
                    
                    // Ambil nama ekskul untuk keperluan log
                    $ekskul = Extracurricular::find($ekskulId);
                    if ($ekskul) $ekskulNames[] = $ekskul->name;
                }
            }

            $ekskulText = !empty($ekskulNames) ? ' untuk ekskul ' . implode(', ', $ekskulNames) : '';

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Tambah pengguna',
                'detail' => $user->name . ' menambahkan pengguna ' . $newUser->name . ' (Username: ' . $newUser->username . ') dengan role ' . ucfirst($newUser->role) . $ekskulText,
            ]);
        });

        return redirect()->route('user-management.index')
            ->with('success', "Pengguna {$validated['name']} berhasil ditambahkan!");
    }

    public function edit($uuid)
    {
        $x['user'] = User::with('extracurriculars.extracurricular')->where('uuid', $uuid)->firstOrFail();
        $x['extracurriculars'] = Extracurricular::orderBy('name')->get();

        return view('role.kesiswaan.contents.user-management.edit', $x);
    }

    public function update(Request $request, $uuid)
    {
        $user = Auth::user();
        $targetUser = User::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username,' . $targetUser->id,
                'email' => 'required|email|max:255|unique:users,email,' . $targetUser->id,
                'phone' => 'required|string|max:20',
                'role' => 'required|in:admin,pembina',
                'extracurricular_ids' => 'nullable|array',
                'extracurricular_ids.*' => 'exists:extracurriculars,id',
                'password' => 'nullable|min:8|confirmed',
            ],
            [
                'name.required' => 'Nama wajib diisi',
                'username.unique' => 'Username sudah digunakan pengguna lain',
                'email.unique' => 'Email sudah digunakan pengguna lain',
                'role.in' => 'Role harus Admin atau Pembina',
                'extracurricular_ids.array' => 'Format ekstrakurikuler tidak valid',
                'extracurricular_ids.*.exists' => 'Salah satu ekstrakurikuler yang dipilih tidak ditemukan',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]
        );

        // Cek jika Admin memilih ekskul
        if ($validated['role'] === 'admin' && $request->filled('extracurricular_ids')) {
            return back()
                ->withErrors(['extracurricular_ids' => 'Admin tidak boleh mengampu ekstrakurikuler'])
                ->withInput();
        }

        // Early Return: Cek apakah ada perubahan data
        $currentEkskulIds = $targetUser->extracurriculars->pluck('extracurricular_id')->sort()->values()->all();
        $newEkskulIds = collect($request->extracurricular_ids ?? [])->map(fn($id) => (int)$id)->sort()->values()->all();

        $isSameData = 
            $targetUser->name === ucwords(strtolower($validated['name'])) &&
            $targetUser->username === $validated['username'] &&
            $targetUser->email === $validated['email'] &&
            $targetUser->phone === $validated['phone'] &&
            $targetUser->role === $validated['role'] &&
            $currentEkskulIds === $newEkskulIds &&
            !$request->filled('password');

        if ($isSameData) {
            return redirect()
                ->route('user-management.index')
                ->with('success', "Data pengguna {$targetUser->name} tidak ada perubahan.");
        }

        $oldName = $targetUser->name;

        DB::transaction(function () use ($validated, $targetUser, $user, $oldName, $request) {
            $updateData = [
                'name' => ucwords(strtolower($validated['name'])),
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => $validated['role'],
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $targetUser->update($updateData);

            // Hapus semua relasi lama
            $targetUser->extracurriculars()->delete();

            // Insert relasi baru (jika pembina)
            $ekskulNames = [];
            if ($validated['role'] === 'pembina' && $request->filled('extracurricular_ids')) {
                foreach ($request->extracurricular_ids as $ekskulId) {
                    ExtracurricularUser::create([
                        'extracurricular_id' => $ekskulId,
                        'user_id' => $targetUser->id,
                    ]);

                    $ekskul = Extracurricular::find($ekskulId);
                    if ($ekskul) $ekskulNames[] = $ekskul->name;
                }
            }

            $ekskulText = !empty($ekskulNames) 
                ? ' dengan ' . count($ekskulNames) . ' ekskul (' . implode(', ', $ekskulNames) . ')' 
                : ' tanpa mengampu ekskul';

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Ubah data pengguna',
                'detail' => $user->name . ' mengubah data pengguna ' . $oldName . 
                        ' menjadi ' . $targetUser->name . 
                        ' (Username: ' . $targetUser->username . ') role ' . 
                        ucfirst($targetUser->role) . $ekskulText,
            ]);
        });

        return redirect()
            ->route('user-management.index')
            ->with('success', "Pengguna {$targetUser->name} berhasil diperbarui!");
    }

    public function profile()
    {
        $user = Auth::user();

        if ($user->role !== 'kesiswaan') {
            abort(403, 'Unauthorized');
        }

        return view('role.kesiswaan.contents.profile.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kesiswaan') {
            return back()->withErrors(['role' => 'Anda tidak diizinkan mengedit profil.']);
        }

        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username,' . $user->id,
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20',
                'password' => 'nullable|min:8|confirmed',
            ],
            [
                'name.required' => 'Nama wajib diisi',
                'username.unique' => 'Username sudah digunakan pengguna lain',
                'email.unique' => 'Email sudah digunakan pengguna lain',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ],
        );

        // Early Return
        if ($user->name === ucwords(strtolower($validated['name'])) && $user->username === $validated['username'] && $user->email === $validated['email'] && $user->phone === $validated['phone'] && !$request->filled('password')) {
            return redirect()->route('profile.index')->with('success', 'Profil tidak ada perubahan.');
        }

        DB::transaction(function () use ($validated, $user, $request) {
            $updateData = [
                'name' => ucwords(strtolower($validated['name'])),
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Ubah profil',
                'detail' => $user->name . ' memperbarui data profilnya sendiri',
            ]);
        });

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui!');
    }

    public function destroy(Request $request, $uuid)
    {
        $user = Auth::user();
        $targetUser = User::where('uuid', $uuid)->firstOrFail();

        $request->validate(['password' => 'required'], ['password.required' => 'Password wajib diisi untuk menghapus pengguna.']);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah!']);
        }

        $extracurriculars = $targetUser->extracurricularList;
        if ($extracurriculars->count() > 0) {
            $names = $extracurriculars->pluck('name')->implode(', ');
            return back()->withErrors([
                'extracurricular' => "Pengguna tidak dapat dihapus karena masih mengampu ekstrakurikuler: {$names}. Hapus atau ganti pembina terlebih dahulu.",
            ]);
        }

        $deletedName = $targetUser->name;
        $deletedUsername = $targetUser->username;

        DB::transaction(function () use ($targetUser, $user, $deletedName, $deletedUsername) {
            $targetUser->delete();

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Hapus pengguna',
                'detail' => $user->name . ' menghapus pengguna ' . $deletedName . ' (Username: ' . $deletedUsername . ')',
            ]);
        });

        return redirect()
            ->route('user-management.index')
            ->with('success', "Pengguna {$deletedName} berhasil dihapus!");
    }

    public function toggleActive(User $user, Request $request)
    {
        $authUser = Auth::user();

        $request->validate(['password' => 'required'], ['password.required' => 'Password wajib diisi untuk mengubah status akun.']);

        if (!Hash::check($request->password, $authUser->password)) {
            return redirect()
                ->route('user-management.index')
                ->withErrors(['error' => 'Password yang Anda masukkan salah!']);
        }

        $newStatus = !$user->is_active;
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        DB::transaction(function () use ($user, $authUser, $newStatus, $statusText) {
            $user->update(['is_active' => $newStatus]);

            Log::create([
                'user_id' => $authUser->id,
                'activity' => 'Ubah status pengguna',
                'detail' => $authUser->name . ' ' . $statusText . ' akun pengguna ' . $user->name . ' (Username: ' . $user->username . ')',
            ]);
        });

        return redirect()
            ->route('user-management.index')
            ->with('success', "Akun {$user->name} berhasil {$statusText}!");
    }
}
