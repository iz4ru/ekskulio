<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\GradeProgressionService;
use App\Services\MembershipTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AcademicYearController extends Controller
{
    public function __construct(
        private GradeProgressionService $gradeService,
        private MembershipTransitionService $membershipService
    ) {}

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
                'password' => 'required_if:status,1',
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

        if ($validated['year-end'] != $validated['year-start'] + 1) {
            return back()
                ->withErrors(['year-end' => 'Tahun ajaran akhir harus tepat 1 tahun setelah tahun awal'])
                ->withInput();
        }

        if ($request->has('status') && $request->status) {
            if (! Hash::check($request->password, Auth::user()->password)) {
                return back()
                    ->withErrors(['password' => 'Password yang Anda masukkan salah!'])
                    ->withInput();
            }
        }

        $academicYear = $validated['year-start'].'/'.$validated['year-end'];
        $semester = strtolower($validated['semester']);

        $exists = AcademicYear::where('year', $academicYear)->where('semester', $semester)->exists();

        if ($exists) {
            return back()
                ->withErrors(['year-start' => 'Tahun ajaran '.$academicYear.' semester '.ucfirst($semester).' sudah ada'])
                ->withInput();
        }

        if ($request->has('status') && $request->status) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create([
            'year' => $academicYear,
            'semester' => $semester,
            'is_active' => $request->has('status') ? true : false,
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Tahun ajaran '.$academicYear.' berhasil ditambahkan!');
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
                'year-end.required' => 'Tahun ajaran akhir wajib diisi',
                'semester.required' => 'Semester wajib dipilih',
            ],
        );

        if ($validated['year-end'] != $validated['year-start'] + 1) {
            return back()
                ->withErrors(['year-end' => 'Tahun ajaran akhir harus tepat 1 tahun setelah tahun awal'])
                ->withInput();
        }

        $newAcademicYear = $validated['year-start'].'/'.$validated['year-end'];
        $semester = strtolower($validated['semester']);

        $exists = AcademicYear::where('year', $newAcademicYear)
            ->where('semester', $semester)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['year-start' => 'Tahun ajaran '.$newAcademicYear.' semester '.ucfirst($semester).' sudah ada'])
                ->withInput();
        }

        $academicYear->update([
            'year' => $newAcademicYear,
            'semester' => $semester,
            'is_active' => $request->has('status') ? true : false,
        ]);

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Tahun ajaran '.$newAcademicYear.' berhasil diperbarui!');
    }

    public function destroy(AcademicYear $academicYear, Request $request)
    {
        if (! Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

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
        if (! Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Password yang anda masukkan salah!']);
        }

        AcademicYear::where('id', '!=', $academicYear->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $academicYear->update(['is_active' => ! $academicYear->is_active]);

        $status = $academicYear->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('academic-years.index')
            ->with('success', "Tahun ajaran {$academicYear->year} - ".ucfirst($academicYear->semester)." berhasil {$status}!");
    }

    public function transitionForm(Request $request)
    {
        $currentAY = AcademicYear::getActiveYear();
        if (! $currentAY) {
            return redirect()->route('academic-years.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        // ✅ Hapus logika getNextYear(), biarkan admin pilih dari dropdown
        $targetAY = $request->filled('new_academic_year_id')
            ? AcademicYear::find($request->new_academic_year_id)
            : null;

        $previewYear = $targetAY ?? $currentAY;
        $preview = $this->gradeService->getTransitionPreview($currentAY, $previewYear);

        // ✅ Filter available years: hanya yang valid secara kronologis
        $availableYears = AcademicYear::where('is_active', false)
            ->where(function ($q) use ($currentAY) {
                $q->where('year', '>', $currentAY->year)
                    ->orWhere(function ($sub) use ($currentAY) {
                        $sub->where('year', $currentAY->year)
                            ->where('semester', '!=', $currentAY->semester);
                    });
            })
            ->orderBy('year')->orderBy('semester')
            ->get();

        return view('role.kesiswaan.contents.academic-year.transition', [
            'currentYear' => $currentAY,
            'targetYear' => $targetAY,
            'studentsReady' => $preview['students'],
            'membershipPreview' => $preview['memberships'],
            'previewInfo' => $preview,
            'availableYears' => $availableYears,
        ]);
    }

    public function processTransition(Request $request)
    {
        $currentAY = AcademicYear::getActiveYear();
        $newAY = AcademicYear::findOrFail($request->new_academic_year_id);

        $request->validate([
            'password' => 'required',
            'new_academic_year_id' => 'required|exists:academic_years,id',
        ], [
            'password.required' => 'Password wajib diisi untuk melanjutkan transisi',
            'new_academic_year_id.required' => 'Tahun ajaran tujuan wajib dipilih',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah!']);
        }

        if (! $currentAY) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran aktif saat ini.']);
        }

        if ($newAY->is_active) {
            return back()->withErrors(['new_academic_year_id' => 'Tahun ajaran tujuan sudah aktif.']);
        }

        if ($newAY->year <= $currentAY->year && $newAY->semester === $currentAY->semester) {
            return back()->withErrors(['new_academic_year_id' => 'Tahun ajaran tujuan harus lebih baru.']);
        }

        $results = $this->gradeService->processYearTransition($currentAY, $newAY);

        if (! empty($results['errors'])) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Terjadi kesalahan: '.implode(', ', $results['errors'])]);
        }

        $message = match ($results['type']) {
            'semester' => "Ganti semester berhasil! {$results['memberships_migrated']} keanggotaan dipindahkan.",
            default => "Transisi berhasil! {$results['promoted']} siswa dinaikkan kelas, {$results['graduated']} siswa kelas XII (lulus), {$results['memberships_migrated']} keanggotaan dipindahkan.",
        };

        return redirect()
            ->route('academic-years.index')
            ->with('success', $message);
    }
}
