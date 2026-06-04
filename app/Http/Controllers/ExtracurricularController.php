<?php

namespace App\Http\Controllers;

use App\Exports\ExtracurricularExport;
use App\Imports\ExtracurricularImport;
use App\Models\Extracurricular;
use App\Models\ExtracurricularCategory;
use App\Models\ExtracurricularSchedule;
use App\Models\ExtracurricularUser;
use App\Models\Log;
use App\Models\Student;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ExtracurricularController extends Controller
{
    public function index(Request $request)
    {
        $query = Extracurricular::with('category');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                ->orWhereHas('category', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            });
        });

        $x['extracurriculars'] = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('role.kesiswaan.contents.extracurricular.index', $x);
    }

    public function detail(Extracurricular $extracurricular)
    {
        $x['extracurricular'] = $extracurricular->load(['category', 'users.user', 'schedules']);

        $x['extracurricularStudent'] = Student::with('studentClass')
            ->leftJoin('student_classes', 'students.class_id', '=', 'student_classes.id')
            ->join('extracurricular_memberships', 'students.id', '=', 'extracurricular_memberships.student_id')
            ->where('extracurricular_memberships.extracurricular_id', $extracurricular->id)
            ->where('extracurricular_memberships.status', 'aktif')
            ->orderBy('student_classes.name', 'asc')
            ->orderBy('students.name', 'asc')
            ->select('students.*')
            ->get();

        return view('role.kesiswaan.contents.extracurricular.detail', $x);
    }

    public function create()
    {
        $x['extracurricularCategories'] = ExtracurricularCategory::orderBy('name')->get();

        $x['extracurricularUsers'] = User::where('role', 'pembina')->orderBy('name')->get();

        return view('role.kesiswaan.contents.extracurricular.create', $x);
    }

    public function import()
    {
        return view('role.kesiswaan.contents.extracurricular.import');
    }

    /**
     * Generate unique 3-letter code dari nama ekstrakurikuler
     */
    public function generateCode($name)
    {
        if (!$name) {
            return response()->json(['code' => '']);
        }

        // Decode URL-encoded name
        $name = urldecode($name);

        // Ambil semua kode yang sudah ada
        $usedCodes = Extracurricular::pluck('code')->toArray();

        $clean = preg_replace('/[^A-Z ]/', '', strtoupper($name));
        $words = array_values(array_filter(explode(' ', $clean)));

        if (empty($words)) {
            return response()->json(['code' => $this->generateFallbackCode($usedCodes)]);
        }

        $suffix = '';
        $last = end($words);
        if (strlen($last) === 1) {
            $suffix = $last;
            array_pop($words);
        }

        $letters = implode('', $words);
        $candidates = [];

        if (count($words) === 1) {
            $candidates[] = substr($letters, 0, 3);
            $candidates[] = substr($letters, 0, 2) . substr($letters, 3, 1);
            $candidates[] = substr($letters, 0, 1) . substr($letters, 2, 2);
        } elseif (count($words) === 2) {
            $candidates[] = substr($words[0], 0, 1) . substr($words[1], 0, 2);
            $candidates[] = substr($words[0], 0, 2) . substr($words[1], 0, 1);
        } else {
            $candidates[] = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
        }

        foreach ($candidates as $code) {
            if ($suffix !== '') {
                $code = substr($code, 0, 2) . $suffix;
            }
            if (strlen($code) === 3 && !in_array($code, $usedCodes)) {
                return response()->json(['code' => $code]);
            }
        }

        // Fallback: tambahkan angka
        $i = 1;
        while (true) {
            $code = substr($letters, 0, 2) . $i;
            if (!in_array($code, $usedCodes)) {
                return response()->json(['code' => $code]);
            }
            $i++;
        }
    }

    private function generateFallbackCode($usedCodes)
    {
        $i = 1;
        while (true) {
            $code = 'EX' . $i;
            if (!in_array($code, $usedCodes)) {
                return $code;
            }
            $i++;
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
 
        $validated = $request->validate(
            [
                'extracurricular_name' => 'required|string|max:255',
                'extracurricular_code' => 'required|string|max:50|unique:extracurriculars,code',
                'category_id'          => 'required|exists:extracurricular_categories,id',
                'user_id'              => 'required|exists:users,id',
                'days'                 => 'required|array|min:1',
                'days.*'               => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
                'description'          => 'nullable|string',
                'award'                => 'nullable|string',
                'status'               => 'nullable|boolean',
            ],
            [
                'extracurricular_name.required' => 'Nama ekstrakurikuler wajib diisi',
                'extracurricular_code.required' => 'Kode ekstrakurikuler wajib diisi',
                'extracurricular_code.unique'   => 'Kode ekstrakurikuler sudah digunakan',
                'category_id.required'          => 'Kategori ekstrakurikuler wajib dipilih',
                'category_id.exists'            => 'Kategori ekstrakurikuler tidak valid',
                'user_id.required'              => 'Pembina ekstrakurikuler wajib dipilih',
                'user_id.exists'                => 'Pembina tidak valid',
                'days.required'                 => 'Minimal pilih 1 hari pelaksanaan',
                'days.min'                      => 'Minimal pilih 1 hari pelaksanaan',
            ],
        );
 
        $name = ucwords(strtoupper($validated['extracurricular_name']));
 
        DB::transaction(function () use ($validated, $request, $user, $name) {
            $extracurricular = Extracurricular::create([
                'name'        => $name,
                'code'        => $validated['extracurricular_code'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
                'award'       => $validated['award'] ?? null,
                'is_active'   => $request->has('status'),
            ]);
 
            ExtracurricularUser::create([
                'extracurricular_id' => $extracurricular->id,
                'user_id'            => $validated['user_id'],
            ]);
 
            foreach ($validated['days'] as $day) {
                ExtracurricularSchedule::create([
                    'extracurricular_id' => $extracurricular->id,
                    'day'                => $day,
                ]);
            }
 
            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Tambah ekstrakurikuler',
                'detail'   => $user->name . ' menambahkan ekstrakurikuler ' . $name . ' (' . $validated['extracurricular_code'] . ')',
            ]);
        });
 
        return redirect()
            ->route('extracurricular.index')
            ->with('success', 'Ekstrakurikuler ' . $name . ' berhasil ditambahkan!');
    }

    public function importStore(Request $request)
    {
        $request->validate(
            [
                'upload' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            ],
            [
                'upload.required' => 'File wajib diupload',
                'upload.mimes' => 'File harus berformat .xlsx, .xls, atau .csv',
                'upload.max' => 'Ukuran file maksimal 2MB',
            ],
        );

        try {
            Excel::import(new ExtracurricularImport(Auth::user()), $request->file('upload'));

            return redirect()->route('extracurricular.index')->with('success', 'Data ekstrakurikuler berhasil diimpor!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()
                ->withErrors(['upload' => $errors])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['upload' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Extracurricular $extracurricular)
    {
        $x['extracurricular'] = $extracurricular->load(['category', 'users.user', 'schedules']);
        $x['extracurricularCategories'] = ExtracurricularCategory::orderBy('name')->get();
        $x['extracurricularUsers'] = User::where('role', 'pembina')->orderBy('name')->get();

        return view('role.kesiswaan.contents.extracurricular.edit', $x);
    }

    public function update(Request $request, Extracurricular $extracurricular)
    {
        $user = Auth::user();
 
        $validated = $request->validate(
            [
                'extracurricular_name' => 'required|string|max:255',
                'extracurricular_code' => 'required|string|max:50|unique:extracurriculars,code,' . $extracurricular->id,
                'category_id'          => 'required|exists:extracurricular_categories,id',
                'user_id'              => 'required|exists:users,id',
                'days'                 => 'required|array|min:1',
                'days.*'               => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
                'description'          => 'nullable|string',
                'award'                => 'nullable|string',
            ],
            [
                'extracurricular_name.required' => 'Nama ekstrakurikuler wajib diisi',
                'extracurricular_code.required' => 'Kode ekstrakurikuler wajib diisi',
                'extracurricular_code.unique'   => 'Kode ekstrakurikuler sudah digunakan',
                'category_id.required'          => 'Kategori ekstrakurikuler wajib dipilih',
                'category_id.exists'            => 'Kategori ekstrakurikuler tidak valid',
                'user_id.required'              => 'Pembina ekstrakurikuler wajib dipilih',
                'user_id.exists'                => 'Pembina tidak valid',
                'days.required'                 => 'Minimal pilih 1 hari pelaksanaan',
                'days.min'                      => 'Minimal pilih 1 hari pelaksanaan',
            ],
        );
 
        $newName     = ucwords(strtoupper($validated['extracurricular_name']));
        $oldName     = $extracurricular->name;
        $oldCode     = $extracurricular->code;
        $newCode     = $validated['extracurricular_code'];
        $oldDays     = $extracurricular->schedules->pluck('day')->sort()->values()->toArray();
        $newDays     = collect($validated['days'])->sort()->values()->toArray();
        $oldUserId   = $extracurricular->users()->first()?->user_id;
        $newUserId   = (int) $validated['user_id'];
 
        // Tidak ada perubahan sama sekali
        if (
            $oldName   === $newName &&
            $oldCode   === $newCode &&
            $oldDays   === $newDays &&
            $oldUserId === $newUserId &&
            ($validated['description'] ?? null) === $extracurricular->description &&
            ($validated['award'] ?? null)       === $extracurricular->award
        ) {
            return redirect()
                ->route('extracurricular.index')
                ->with('success', 'Ekstrakurikuler ' . $newName . ' berhasil diperbarui!');
        }
 
        DB::transaction(function () use ($validated, $extracurricular, $user, $newName, $oldName, $oldCode, $newCode) {
            $extracurricular->update([
                'name'        => $newName,
                'code'        => $newCode,
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
                'award'       => $validated['award'] ?? null,
            ]);
 
            // Sync pembina
            $extracurricular->users()->delete();
            ExtracurricularUser::create([
                'extracurricular_id' => $extracurricular->id,
                'user_id'            => $validated['user_id'],
            ]);
 
            // Sync jadwal
            $extracurricular->schedules()->delete();
            foreach ($validated['days'] as $day) {
                ExtracurricularSchedule::create([
                    'extracurricular_id' => $extracurricular->id,
                    'day'                => $day,
                ]);
            }
 
            $logDetail = $oldName !== $newName || $oldCode !== $newCode
                ? $user->name . ' mengubah ekstrakurikuler ' . $oldName . ' (' . $oldCode . ') menjadi ' . $newName . ' (' . $newCode . ')'
                : $user->name . ' memperbarui data ekstrakurikuler ' . $newName . ' (' . $newCode . ')';
 
            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Ubah ekstrakurikuler',
                'detail'   => $logDetail,
            ]);
        });
 
        return redirect()
            ->route('extracurricular.index')
            ->with('success', 'Ekstrakurikuler ' . $newName . ' berhasil diperbarui!');
    }

    public function destroy(Request $request, Extracurricular $extracurricular)
    {
        $user = Auth::user();
 
        $request->validate(
            ['password' => 'required'],
            ['password.required' => 'Password wajib diisi untuk menghapus ekstrakurikuler.'],
        );
 
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah!']);
        }
 
        $studentCount = $extracurricular->students()->count();
 
        if ($studentCount > 0) {
            return back()->withErrors([
                'student' => 'Ekstrakurikuler tidak dapat dihapus karena masih memiliki ' . $studentCount . ' siswa. Hapus atau pindahkan siswa terlebih dahulu.',
            ]);
        }
 
        $name = $extracurricular->name;
        $code = $extracurricular->code;
 
        DB::transaction(function () use ($user, $extracurricular, $name, $code) {
            $extracurricular->users()->delete();
            $extracurricular->schedules()->delete();
            $extracurricular->delete();
 
            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Hapus ekstrakurikuler',
                'detail'   => $user->name . ' menghapus ekstrakurikuler ' . $name . ' (' . $code . ') (ID: ' . $extracurricular->id . ')',
            ]);
        });
 
        return redirect()
            ->route('extracurricular.index')
            ->with('success', 'Ekstrakurikuler ' . $name . ' berhasil dihapus!');
    }

    public function toggleActive(Extracurricular $extracurricular, Request $request)
    {
        $user = Auth::user();
 
        if (!Hash::check($request->password, $user->password)) {
            return redirect()
                ->route('extracurricular.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }
 
        $newStatus = !$extracurricular->is_active;
        $status    = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
 
        DB::transaction(function () use ($user, $extracurricular, $newStatus, $status) {
            $extracurricular->update(['is_active' => $newStatus]);
 
            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Toggle status ekstrakurikuler',
                'detail'   => $user->name . ' ' . $status . ' ekstrakurikuler ' . $extracurricular->name,
            ]);
        });
 
        return redirect()
            ->route('extracurricular.index')
            ->with('success', "Ekstrakurikuler {$extracurricular->name} berhasil {$status}!");
    }

    public function export()
    {
        return Excel::download(new ExtracurricularExport(), 'data-ekstrakurikuler.xlsx');
    }
}
