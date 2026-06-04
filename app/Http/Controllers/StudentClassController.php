<?php

namespace App\Http\Controllers;

use App\Imports\StudentClassImport;
use App\Models\Log;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class StudentClassController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentClass::withCount('students');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            });
        });

        $x['classes'] = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('role.kesiswaan.contents.student-class.index', $x);
    }

    public function create()
    {
        return view('role.kesiswaan.contents.student-class.create');
    }

    public function import()
    {
        return view('role.kesiswaan.contents.student-class.import');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate(
            ['class_name' => 'required|string|max:255|unique:student_classes,name'],
            [
                'class_name.required' => 'Nama kelas wajib diisi',
                'class_name.unique'   => 'Nama kelas sudah digunakan',
            ],
        );

        DB::transaction(function () use ($validated, $request, $user) {
            $class = StudentClass::create([
                'name'      => $validated['class_name'],
                'is_active' => $request->has('is_active'),
            ]);

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Tambah kelas',
                'detail'   => $user->name . ' menambahkan kelas ' . $class->name,
            ]);
        });

        return redirect()
            ->route('student-class.index')
            ->with('success', 'Kelas ' . $validated['class_name'] . ' berhasil ditambahkan!');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'upload' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'upload.required' => 'File wajib diupload',
            'upload.mimes' => 'File harus berformat .xlsx, .xls, atau .csv',
            'upload.max' => 'Ukuran file maksimal 2MB',
        ]);

        try {
            Excel::import(new StudentClassImport(Auth::user()), $request->file('upload'));

            return redirect()
                ->route('student-class.index')
                ->with('success', 'Data kelas berhasil diimpor!');
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

    public function edit(StudentClass $studentClass)
    {
        return view('role.kesiswaan.contents.student-class.edit', compact('studentClass'));
    }

    public function update(Request $request, StudentClass $studentClass)
    {
        $user = Auth::user();

        $validated = $request->validate(
            ['class_name' => 'required|string|max:255|unique:student_classes,name,' . $studentClass->id],
            [
                'class_name.required' => 'Nama kelas wajib diisi',
                'class_name.unique'   => 'Nama kelas sudah digunakan',
            ],
        );

        if ($studentClass->name === $validated['class_name']) {
            return redirect()
                ->route('student-class.index')
                ->with('success', 'Kelas ' . $validated['class_name'] . ' berhasil diperbarui!');
        }

        $oldName = $studentClass->name;

        DB::transaction(function () use ($validated, $studentClass, $user, $oldName) {
            $studentClass->update(['name' => $validated['class_name']]);

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Ubah kelas',
                'detail'   => $user->name . ' mengubah kelas ' . $oldName . ' menjadi ' . $validated['class_name'],
            ]);
        });

        return redirect()
            ->route('student-class.index')
            ->with('success', 'Kelas ' . $validated['class_name'] . ' berhasil diperbarui!');
    }

    public function destroy(Request $request, StudentClass $studentClass)
    {
        $user = Auth::user();

        $request->validate(
            ['password' => 'required'],
            ['password.required' => 'Password wajib diisi untuk menghapus kelas.'],
        );

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah!']);
        }

        $name         = $studentClass->name;
        $studentCount = $studentClass->students()->count();

        if ($studentCount > 0) {
            return back()->withErrors([
                'student' => 'Kelas tidak dapat dihapus karena masih memiliki ' . $studentCount . ' siswa. Hapus atau pindahkan siswa terlebih dahulu.',
            ]);
        }

        DB::transaction(function () use ($studentClass, $user, $name) {
            $studentClass->delete();

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Hapus kelas',
                'detail'   => $user->name . ' menghapus kelas ' . $name . ' (ID: ' . $studentClass->id . ')',
            ]);
        });

        return redirect()
            ->route('student-class.index')
            ->with('success', 'Kelas ' . $name . ' berhasil dihapus!');
    }

    public function toggleActive(StudentClass $studentClass, Request $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return redirect()
                ->route('student-class.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        DB::transaction(function () use ($studentClass, $user) {
            $studentClass->update(['is_active' => !$studentClass->is_active]);

            $status = $studentClass->is_active ? 'mengaktifkan' : 'menonaktifkan';

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Toggle status kelas',
                'detail'   => $user->name . ' ' . $status . ' kelas ' . $studentClass->name,
            ]);
        });

        $status = $studentClass->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('student-class.index')
            ->with('success', "Kelas {$studentClass->name} berhasil {$status}!");
    }
}
