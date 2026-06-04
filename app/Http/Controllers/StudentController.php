<?php

namespace App\Http\Controllers;

use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Imports\StudentImport;
use App\Models\AcademicYear;
use App\Models\Log;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['studentClass', 'memberships'])
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'active') {
                    return $q->active()->notGraduated();
                }
                return $q->where('status', $request->status);
            })
            ->when($request->filled('grade'), fn($q) => $q->byGrade($request->grade))
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])->orWhereRaw('LOWER(id_number) LIKE ?', ["%{$search}%"]);
                });
            });

        if (!$request->filled('include_graduated')) {
            $query->notGraduated();
        }

        $x['students'] = $query->orderBy('grade')->orderBy('name')->paginate(15)->withQueryString();
        $x['studentClasses'] = StudentClass::where('is_active', true)->orderBy('name')->get();

        return view('role.kesiswaan.contents.student.index', $x);
    }

    public function create()
    {
        $activeAY = AcademicYear::getActiveYear();

        $x['studentClasses'] = StudentClass::where('is_active', true)->orderBy('name')->get();
        $x['grades'] = [StudentGrade::X->value, StudentGrade::XI->value, StudentGrade::XII->value];
        $x['activeAY'] = $activeAY;
        $x['calculatedGrade'] = $activeAY ? Student::calculateGradeFromEnrollment((int) date('Y'), (int) substr($activeAY->year, 0, 4)) : null;

        return view('role.kesiswaan.contents.student.create', $x);
    }

    public function import()
    {
        $x['studentClasses'] = StudentClass::orderBy('name')->get();
        $x['grades'] = [StudentGrade::X->value, StudentGrade::XI->value, StudentGrade::XII->value];

        return view('role.kesiswaan.contents.student.import', $x);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'id_number' => 'required|string|max:50|unique:students,id_number',
                'class_id' => 'required|exists:student_classes,id',
                'enrollment_year' => 'required|integer|min:2000|max:2099',
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
            ],
        );

        $grade = $request->filled('grade_override') ? $request->grade_override : Student::calculateGradeFromEnrollment($validated['enrollment_year']);

        DB::transaction(function () use ($validated, $grade, $user) {
            $student = Student::create([
                'name' => strtoupper($validated['name']),
                'id_number' => $validated['id_number'],
                'class_id' => $validated['class_id'],
                'grade' => $grade,
                'enrollment_year' => $validated['enrollment_year'],
                'status' => StudentStatus::AKTIF->value,
                'award' => $validated['award'] ?? null,
            ]);

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Tambah siswa',
                'detail' => $user->name . ' menambahkan siswa ' . $student->name . ' (NIS: ' . $student->id_number . ')',
            ]);
        });

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
            Excel::import(new StudentImport($request->class_id, Auth::user()), $request->file('upload'));

            return redirect()->route('student.index')->with('success', 'Data siswa berhasil diimpor!');
        } catch (ValidationException $e) {
            $errors = collect($e->failures())->map(fn($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))->toArray();

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
        $activeAY = AcademicYear::getActiveYear();

        $x['student'] = $student;
        $x['studentClasses'] = StudentClass::where('is_active', true)->orderBy('name')->get();
        $x['grades'] = [StudentGrade::X->value, StudentGrade::XI->value, StudentGrade::XII->value];
        $x['activeAY'] = $activeAY;
        $x['calculatedGrade'] = $activeAY ? Student::calculateGradeFromEnrollment($student->enrollment_year, (int) substr($activeAY->year, 0, 4)) : null;

        return view('role.kesiswaan.contents.student.edit', $x);
    }

    public function update(Request $request, Student $student)
    {
        $user = Auth::user();

        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'id_number' => 'required|string|max:50|unique:students,id_number,' . $student->id . ',id',
                'class_id' => 'required|exists:student_classes,id',
                'grade' => 'required|in:X,XI,XII',
                'status' => 'required|in:aktif,lulus,mutasi',
                'enrollment_year' => 'required|integer|min:2000|max:2099',
                'award' => 'nullable|string|max:1000',
            ],
            [
                'name.required' => 'Nama siswa wajib diisi',
                'id_number.required' => 'NIS wajib diisi',
                'id_number.unique' => 'NIS sudah terdaftar',
                'class_id.required' => 'Kelas wajib dipilih',
                'class_id.exists' => 'Kelas tidak valid',
                'grade.required' => 'Tingkat/Kelas wajib dipilih',
                'grade.in' => 'Tingkat/Kelas tidak valid',
                'status.required' => 'Status wajib dipilih',
                'status.in' => 'Status tidak valid',
                'enrollment_year.required' => 'Tahun masuk wajib diisi',
                'enrollment_year.integer' => 'Tahun masuk harus berupa angka',
            ],
        );

        // Early return kalau tidak ada perubahan
        if ($student->name === strtoupper($validated['name']) && $student->id_number === $validated['id_number'] && $student->class_id == $validated['class_id'] && $student->grade_value === $validated['grade'] && $student->status_label === $validated['status'] && $student->enrollment_year == $validated['enrollment_year'] && $student->award === ($validated['award'] ?? null)) {
            return redirect()->route('student.index')->with('success', 'Data siswa berhasil diperbarui');
        }

        $oldName = $student->name;

        DB::transaction(function () use ($validated, $student, $user, $oldName) {
            $student->update([
                'name' => strtoupper($validated['name']),
                'id_number' => $validated['id_number'],
                'class_id' => $validated['class_id'],
                'grade' => $validated['grade'],
                'status' => $validated['status'],
                'enrollment_year' => $validated['enrollment_year'],
                'award' => $validated['award'] ?? null,
            ]);

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Ubah data siswa',
                'detail' => $user->name . ' mengubah data siswa ' . $oldName . ' (NIS: ' . $student->id_number . ')',
            ]);
        });

        return redirect()->route('student.index')->with('success', 'Data siswa berhasil diperbarui');
    }

    public function destroy(Request $request, Student $student)
    {
        $user = Auth::user();

        $request->validate(['password' => 'required'], ['password.required' => 'Password wajib diisi untuk menghapus data']);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah']);
        }

        $name = $student->name;
        $idNumber = $student->id_number;

        DB::transaction(function () use ($student, $user, $name, $idNumber) {
            $student->delete();

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Hapus siswa',
                'detail' => $user->name . ' menghapus siswa ' . $name . ' (NIS: ' . $idNumber . ')',
            ]);
        });

        return redirect()->route('student.index')->with('success', 'Siswa berhasil dihapus');
    }
}
