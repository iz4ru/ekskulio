<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use App\Imports\StudentImport;
use App\Models\Extracurricular;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index()
    {
        $x['students'] = Student::with(['studentClass', 'extracurricular'])
            ->orderBy('name')
            ->get();

        return view('role.kesiswaan.contents.student.index', $x);
    }

    public function create()
    {
        $x['studentClasses'] = StudentClass::where('is_active', true)->orderBy('name')->get();

        $x['extracurriculars'] = Extracurricular::where('is_active', true)->orderBy('name')->get();

        return view('role.kesiswaan.contents.student.create', $x);
    }

    public function import()
    {
        $x['studentClasses'] = StudentClass::orderBy('name')->get();

        return view('role.kesiswaan.contents.student.import', $x);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'id_number' => 'required|string|max:50|unique:students,id_number',
                'class_id' => 'required|exists:student_classes,id',
                'enrollment_year' => 'required|integer|min:2000|max:2099',
                'extracurricular_id' => 'nullable|exists:extracurriculars,id',
                'award' => 'nullable|string|max:1000',
            ],
            [
                'name.required' => 'Nama siswa wajib diisi',
                'id_number.required' => 'NIS wajib diisi',
                'id_number.unique' => 'NIS sudah terdaftar',
                'class_id.required' => 'Kelas wajib dipilih',
                'class_id.exists' => 'Kelas tidak valid',
                'enrollment_year.required' => 'Tahun masuk wajib diisi',
                'enrollment_year.integer' => 'Tahun masuk harus berupa angka',
                'extracurricular_id.exists' => 'Ekstrakurikuler tidak valid',
            ],
        );

        Student::create([
            'name' => strtoupper($validated['name']),
            'id_number' => $validated['id_number'],
            'class_id' => $validated['class_id'],
            'enrollment_year' => $validated['enrollment_year'],
            'extracurricular_id' => $validated['extracurricular_id'] ?? null,
            'award' => $validated['award'] ?? null,
        ]);

        return redirect()->route('student.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function importStore(Request $request)
    {
        $request->validate(
            [
                'upload' => 'required|file|mimes:xlsx,xls,csv|max:2048',
                'class_id' => 'nullable|integer|exists:student_classes,id',
            ],
            [
                'upload.required' => 'File wajib diupload',
                'upload.mimes' => 'File harus berformat .xlsx, .xls, atau .csv',
                'upload.max' => 'Ukuran file maksimal 2MB',
                'class_id.exists' => 'Kelas tidak ditemukan',
            ],
        );

        try {
            // Pass class_id ke StudentImport
            Excel::import(new StudentImport($request->class_id), $request->file('upload'));

            return redirect()->route('student.index')->with('success', 'Data siswa berhasil diimpor!');
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

    public function edit(Student $student)
    {
        $x['student'] = $student;
        $x['studentClasses'] = StudentClass::where('is_active', true)->orderBy('name')->get();

        $x['extracurriculars'] = Extracurricular::where('is_active', true)->orderBy('name')->get();

        return view('role.kesiswaan.contents.student.edit', $x);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'id_number' => 'required|string|max:50|unique:students,id_number,' . $student->id . ',id',
                'class_id' => 'required|exists:student_classes,id',
                'enrollment_year' => 'required|integer|min:2000|max:2099',
                'extracurricular_id' => 'nullable|exists:extracurriculars,id',
                'award' => 'nullable|string|max:1000',
            ],
            [
                'name.required' => 'Nama siswa wajib diisi',
                'id_number.required' => 'NIS wajib diisi',
                'id_number.unique' => 'NIS sudah terdaftar',
                'class_id.required' => 'Kelas wajib dipilih',
                'class_id.exists' => 'Kelas tidak valid',
                'enrollment_year.required' => 'Tahun masuk wajib diisi',
                'enrollment_year.integer' => 'Tahun masuk harus berupa angka',
                'extracurricular_id.exists' => 'Ekstrakurikuler tidak valid',
            ],
        );

        $student->update([
            'name' => strtoupper($validated['name']),
            'id_number' => $validated['id_number'],
            'class_id' => $validated['class_id'],
            'enrollment_year' => $validated['enrollment_year'],
            'extracurricular_id' => $validated['extracurricular_id'] ?? null,
            'award' => $validated['award'] ?? null,
        ]);

        return redirect()->route('student.index')->with('success', 'Data siswa berhasil diperbarui');
    }

    public function destroy(Request $request, Student $student)
    {
        $request->validate(
            [
                'password' => 'required',
            ],
            [
                'password.required' => 'Password wajib diisi untuk menghapus data',
            ],
        );

        // Verify password (sesuaikan dengan auth user Anda)
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah']);
        }

        $student->delete();

        return redirect()->route('student.index')->with('success', 'Siswa berhasil dihapus');
    }
}
