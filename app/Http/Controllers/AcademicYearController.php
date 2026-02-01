<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AcademicYearController extends Controller
{
    public function index()
    {
        $x['academicYears'] = AcademicYear::orderBy('year', 'desc')->orderBy('semester', 'desc')->get();

        return view('role.kesiswaan.contents.academic-year.index', $x);
    }

    public function create()
    {
        return view('role.kesiswaan.contents.academic-year.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'year-start' => 'required|integer|min:2000|max:2099',
                'year-end' => 'required|integer|min:2000|max:2099|gt:year-start',
                'semester' => 'required|in:Ganjil,Genap',
                'status' => 'nullable|boolean',
                'password' => 'required_if:status,1', // Password wajib jika status diaktifkan
            ],
            [
                'year-start.required' => 'Tahun ajaran awal wajib diisi',
                'year-start.integer' => 'Tahun ajaran awal harus berupa angka',
                'year-start.min' => 'Tahun ajaran awal minimal 2000',
                'year-start.max' => 'Tahun ajaran awal maksimal 2099',
                'year-end.required' => 'Tahun ajaran akhir wajib diisi',
                'year-end.integer' => 'Tahun ajaran akhir harus berupa angka',
                'year-end.min' => 'Tahun ajaran akhir minimal 2000',
                'year-end.max' => 'Tahun ajaran akhir maksimal 2099',
                'year-end.gt' => 'Tahun ajaran akhir harus lebih besar dari tahun awal',
                'semester.required' => 'Semester wajib dipilih',
                'semester.in' => 'Semester harus Ganjil atau Genap',
                'password.required_if' => 'Password wajib diisi untuk mengaktifkan status',
            ],
        );

        // Validasi tambahan: pastikan tahun akhir = tahun awal + 1
        if ($validated['year-end'] != $validated['year-start'] + 1) {
            return back()
                ->withErrors([
                    'year-end' => 'Tahun ajaran akhir harus tepat 1 tahun setelah tahun awal',
                ])
                ->withInput();
        }

        // Jika status akan diaktifkan, validasi password
        if ($request->has('status') && $request->status) {
            if (!Hash::check($request->password, Auth::user()->password)) {
                return back()
                    ->withErrors([
                        'password' => 'Password yang Anda masukkan salah!',
                    ])
                    ->withInput();
            }
        }

        // Format tahun ajaran menjadi "2023/2024"
        $academicYear = $validated['year-start'] . '/' . $validated['year-end'];

        // Convert semester ke lowercase untuk sesuai dengan enum database
        $semester = strtolower($validated['semester']);

        // Cek apakah kombinasi tahun dan semester sudah ada
        $exists = AcademicYear::where('year', $academicYear)->where('semester', $semester)->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'year-start' => 'Tahun ajaran ' . $academicYear . ' semester ' . ucfirst($semester) . ' sudah ada',
                ])
                ->withInput();
        }

        // Jika status di-check, nonaktifkan tahun ajaran lain
        if ($request->has('status') && $request->status) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        // Simpan data
        AcademicYear::create([
            'year' => $academicYear,
            'semester' => $semester,
            'is_active' => $request->has('status') ? true : false,
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Tahun ajaran ' . $academicYear . ' berhasil ditambahkan!');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('role.kesiswaan.contents.academic-year.edit', compact('academicYear'));
    }

    public function update(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        $validated = $request->validate(
            [
                'year-start' => 'required|integer|min:2000|max:2099',
                'year-end' => 'required|integer|min:2000|max:2099|gt:year-start',
                'semester' => 'required|in:Ganjil,Genap',
            ],
            [
                'year-start.required' => 'Tahun ajaran awal wajib diisi',
                'year-start.integer' => 'Tahun ajaran awal harus berupa angka',
                'year-start.min' => 'Tahun ajaran awal minimal 2000',
                'year-start.max' => 'Tahun ajaran awal maksimal 2099',
                'year-end.required' => 'Tahun ajaran akhir wajib diisi',
                'year-end.integer' => 'Tahun ajaran akhir harus berupa angka',
                'year-end.min' => 'Tahun ajaran akhir minimal 2000',
                'year-end.max' => 'Tahun ajaran akhir maksimal 2099',
                'year-end.gt' => 'Tahun ajaran akhir harus lebih besar dari tahun awal',
                'semester.required' => 'Semester wajib dipilih',
                'semester.in' => 'Semester harus Ganjil atau Genap',
            ],
        );

        // Validasi tambahan: pastikan tahun akhir = tahun awal + 1
        if ($validated['year-end'] != $validated['year-start'] + 1) {
            return back()
                ->withErrors([
                    'year-end' => 'Tahun ajaran akhir harus tepat 1 tahun setelah tahun awal',
                ])
                ->withInput();
        }

        // Format tahun ajaran menjadi "2023/2024"
        $newAcademicYear = $validated['year-start'] . '/' . $validated['year-end'];

        // Convert semester ke lowercase untuk sesuai dengan enum database
        $semester = strtolower($validated['semester']);

        // Cek apakah kombinasi tahun dan semester sudah ada (kecuali data yang sedang diedit)
        $exists = AcademicYear::where('year', $newAcademicYear)->where('semester', $semester)->where('id', '!=', $id)->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'year-start' => 'Tahun ajaran ' . $newAcademicYear . ' semester ' . ucfirst($semester) . ' sudah ada',
                ])
                ->withInput();
        }

        // Update data
        $academicYear->update([
            'year' => $newAcademicYear,
            'semester' => $semester,
            'is_active' => $request->has('status') ? true : false,
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Tahun ajaran ' . $newAcademicYear . ' berhasil diperbarui!');
    }

    public function destroy(AcademicYear $academicYear, Request $request)
    {
        // Validasi password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        // Cek apakah tahun ajaran sedang aktif
        if ($academicYear->is_active) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Tahun ajaran tidak bisa dihapus karena sedang aktif.']);
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus!');
    }

    public function toggleActive(AcademicYear $academicYear, Request $request)
    {
        // Validasi password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        // Nonaktifkan semua tahun ajaran lain
        AcademicYear::where('id', '!=', $academicYear->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Toggle status
        $academicYear->update([
            'is_active' => !$academicYear->is_active,
        ]);

        $status = $academicYear->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('academic-years.index')
            ->with('success', "Tahun ajaran {$academicYear->year} - " . ucfirst($academicYear->semester) . " berhasil {$status}!");
    }
}
