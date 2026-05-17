<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\Extracurricular;
use App\Models\ExtracurricularUser;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExtracurricularImport;
use App\Models\ExtracurricularCategory;
use App\Models\ExtracurricularSchedule;

class ExtracurricularController extends Controller
{
    public function index()
    {
        $x['extracurriculars'] = Extracurricular::with('category')->orderBy('name')->get();

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
        $validated = $request->validate(
            [
                'extracurricular_name' => 'required|string|max:255',
                'extracurricular_code' => 'required|string|max:50|unique:extracurriculars,code',
                'category_id' => 'required|exists:extracurricular_categories,id',
                'user_id' => 'required|exists:users,id',
                'days' => 'required|array|min:1',
                'days.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
                'description' => 'nullable|string',
                'award' => 'nullable|string',
                'status' => 'nullable|boolean',
            ],
            [
                'extracurricular_name.required' => 'Nama ekstrakurikuler wajib diisi',
                'extracurricular_code.required' => 'Kode ekstrakurikuler wajib diisi',
                'extracurricular_code.unique' => 'Kode ekstrakurikuler sudah digunakan',
                'category_id.required' => 'Kategori ekstrakurikuler wajib dipilih',
                'category_id.exists' => 'Kategori ekstrakurikuler tidak valid',
                'user_id.required' => 'Pembina ekstrakurikuler wajib dipilih',
                'user_id.exists' => 'Pembina tidak valid',
                'days.required' => 'Minimal pilih 1 hari pelaksanaan',
                'days.min' => 'Minimal pilih 1 hari pelaksanaan',
            ],
        );

        $extracurricular = Extracurricular::create([
            'name' => ucwords(strtoupper($validated['extracurricular_name'])),
            'code' => $validated['extracurricular_code'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'award' => $validated['award'],
            'is_active' => $request->has('status') ? true : false,
        ]);

        ExtracurricularUser::create([
            'extracurricular_id' => $extracurricular->id,
            'user_id' => $validated['user_id'],
        ]);

        foreach ($validated['days'] as $day) {
            ExtracurricularSchedule::create([
                'extracurricular_id' => $extracurricular->id,
                'day' => $day,
            ]);
        }

        return redirect()
            ->route('extracurricular.index')
            ->with('success', 'Ekstrakurikuler ' . ucwords(strtoupper($validated['extracurricular_name'])) . ' berhasil ditambahkan!');
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
            Excel::import(new ExtracurricularImport(), $request->file('upload'));

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
        $validated = $request->validate(
            [
                'extracurricular_name' => 'required|string|max:255',
                'extracurricular_code' => 'required|string|max:50|unique:extracurriculars,code,' . $extracurricular->id,
                'category_id' => 'required|exists:extracurricular_categories,id',
                'user_id' => 'required|exists:users,id',
                'days' => 'required|array|min:1',
                'days.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
                'description' => 'nullable|string',
                'award' => 'nullable|string',
            ],
            [
                'extracurricular_name.required' => 'Nama ekstrakurikuler wajib diisi',
                'extracurricular_code.required' => 'Kode ekstrakurikuler wajib diisi',
                'extracurricular_code.unique' => 'Kode ekstrakurikuler sudah digunakan',
                'category_id.required' => 'Kategori ekstrakurikuler wajib dipilih',
                'category_id.exists' => 'Kategori ekstrakurikuler tidak valid',
                'user_id.required' => 'Pembina ekstrakurikuler wajib dipilih',
                'user_id.exists' => 'Pembina tidak valid',
                'days.required' => 'Minimal pilih 1 hari pelaksanaan',
                'days.min' => 'Minimal pilih 1 hari pelaksanaan',
            ],
        );

        // Update ekstrakurikuler
        $extracurricular->update([
            'name' => ucwords(strtoupper($validated['extracurricular_name'])),
            'code' => $validated['extracurricular_code'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'award' => $validated['award'],
        ]);

        // Update pembina (hapus lama, tambah baru)
        $extracurricular->users()->delete();
        ExtracurricularUser::create([
            'extracurricular_id' => $extracurricular->id,
            'user_id' => $validated['user_id'],
        ]);

        // Update jadwal (hapus lama, tambah baru)
        $extracurricular->schedules()->delete();
        foreach ($validated['days'] as $day) {
            ExtracurricularSchedule::create([
                'extracurricular_id' => $extracurricular->id,
                'day' => $day,
            ]);
        }

        return redirect()
            ->route('extracurricular.index')
            ->with('success', 'Ekstrakurikuler ' . ucwords(strtoupper($validated['extracurricular_name'])) . ' berhasil diperbarui!');
    }

    public function destroy(Request $request, Extracurricular $extracurricular)
    {
        // Validasi password
        $request->validate(
            [
                'password' => 'required',
            ],
            [
                'password.required' => 'Password wajib diisi untuk menghapus ekstrakurikuler.',
            ],
        );

        // Cek password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah!',
            ]);
        }

        // Simpan nama ekstrakurikuler
        $name = $extracurricular->name;

        $studentCount = $extracurricular->students()->count();

        if ($studentCount > 0) {
            return back()->withErrors([
                'student' => 'Ekstrakurikuler tidak dapat dihapus karena masih memiliki ' . $studentCount . ' siswa. Hapus atau pindahkan siswa terlebih dahulu.',
            ]);
        }

        // Hapus relasi terkait terlebih dahulu
        $extracurricular->users()->delete(); // Hapus pembina
        $extracurricular->schedules()->delete(); // Hapus jadwal

        // Hapus ekstrakurikuler
        $extracurricular->delete();

        return redirect()
            ->route('extracurricular.index')
            ->with('success', 'Ekstrakurikuler ' . $name . ' berhasil dihapus!');
    }

    public function toggleActive(Extracurricular $extracurricular, Request $request)
    {
        // Validasi password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('extracurricular.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        // Toggle status
        $extracurricular->update([
            'is_active' => !$extracurricular->is_active,
        ]);

        $status = $extracurricular->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()
            ->route('extracurricular.index')
            ->with('success', "Ekstrakurikuler {$extracurricular->name} berhasil {$status}!");
    }
}
