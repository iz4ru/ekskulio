<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use Illuminate\Http\Request;
use App\Imports\StudentClassImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class StudentClassController extends Controller
{
    public function index()
    {
        $x['classes'] = StudentClass::withCount('students')->orderBy('name')->get();

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
        $validated = $request->validate(
            [
                'class_name' => 'required|string|max:255|unique:student_classes,name',
            ],
            [
                'class_name.required' => 'Nama kelas wajib diisi',
                'class_name.unique' => 'Nama kelas sudah digunakan',
            ],
        );

        StudentClass::create([
            'name' => $validated['class_name'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

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
            Excel::import(new StudentClassImport, $request->file('upload'));

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
        $validated = $request->validate(
            [
                'class_name' => 'required|string|max:255|unique:student_classes,name,' . $studentClass->id,
            ],
            [
                'class_name.required' => 'Nama kelas wajib diisi',
                'class_name.unique' => 'Nama kelas sudah digunakan',
            ],
        );

        $studentClass->update([
            'name' => $validated['class_name'],
        ]);

        return redirect()
            ->route('student-class.index')
            ->with('success', 'Kelas ' . $validated['class_name'] . ' berhasil diperbarui!');
    }

    public function destroy(Request $request, StudentClass $studentClass)
    {
        // Validasi password
        $request->validate(
            [
                'password' => 'required',
            ],
            [
                'password.required' => 'Password wajib diisi untuk menghapus kelas.',
            ],
        );

        // Cek password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah!',
            ]);
        }

        // ✅ FIX: Simpan nama dulu sebelum cek students
        $name = $studentClass->name;

        // Cek apakah kelas masih punya siswa
        $studentCount = $studentClass->students()->count();

        if ($studentCount > 0) {
            return back()->withErrors([
                'student' => 'Kelas tidak dapat dihapus karena masih memiliki ' . $studentCount . ' siswa. Hapus atau pindahkan siswa terlebih dahulu.',
            ]);
        }

        // Hapus kelas
        $studentClass->delete();

        return redirect()
            ->route('student-class.index')
            ->with('success', 'Kelas ' . $name . ' berhasil dihapus!');
    }

    public function toggleActive(StudentClass $studentClass, Request $request)
    {
        // Validasi password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('student-class.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        // Toggle status
        $studentClass->update([
            'is_active' => !$studentClass->is_active,
        ]);

        $status = $studentClass->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()
            ->route('student-class.index')
            ->with('success', "Kelas {$studentClass->name} berhasil {$status}!");
    }
}
