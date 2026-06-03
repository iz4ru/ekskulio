<?php

namespace App\Http\Controllers;

use App\Exports\ActiveStudentExport;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use App\Services\AcademicYearClosureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AcademicYearController extends Controller
{
    public function __construct(private AcademicYearClosureService $closureService) {}

    public function index(Request $request)
    {
        $activeAY = AcademicYear::getActiveYear();

        $query = AcademicYear::query();

        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(year) LIKE ?', ["%{$search}%"])->orWhereRaw('LOWER(semester) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderByRaw("
            CASE
                WHEN is_active = 1 THEN 0
                WHEN year > (SELECT year FROM academic_years WHERE is_active = 1 LIMIT 1) THEN 1
                WHEN year = (SELECT year FROM academic_years WHERE is_active = 1 LIMIT 1)
                    AND semester = 'genap'
                    AND (SELECT semester FROM academic_years WHERE is_active = 1 LIMIT 1) = 'ganjil' THEN 2
                ELSE 3
            END,
            year DESC,
            CASE semester
                WHEN 'ganjil' THEN 1
                WHEN 'genap' THEN 2
            END
        ");

        $academicYears = $query->paginate(15)->withQueryString();

        return view('role.kesiswaan.contents.academic-year.index', compact('academicYears', 'activeAY'));
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
            if (!Hash::check($request->password, Auth::user()->password)) {
                return back()
                    ->withErrors(['password' => 'Password yang Anda masukkan salah!'])
                    ->withInput();
            }
        }

        $academicYear = $validated['year-start'] . '/' . $validated['year-end'];
        $semester = strtolower($validated['semester']);

        $exists = AcademicYear::where('year', $academicYear)->where('semester', $semester)->exists();

        if ($exists) {
            return back()
                ->withErrors(['year-start' => 'Tahun ajaran ' . $academicYear . ' semester ' . ucfirst($semester) . ' sudah ada'])
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
                'year-end.required' => 'Tahun ajaran akhir wajib diisi',
                'semester.required' => 'Semester wajib dipilih',
            ],
        );

        if ($validated['year-end'] != $validated['year-start'] + 1) {
            return back()
                ->withErrors(['year-end' => 'Tahun ajaran akhir harus tepat 1 tahun setelah tahun awal'])
                ->withInput();
        }

        $newAcademicYear = $validated['year-start'] . '/' . $validated['year-end'];
        $semester = strtolower($validated['semester']);

        $exists = AcademicYear::where('year', $newAcademicYear)->where('semester', $semester)->where('id', '!=', $id)->exists();

        if ($exists) {
            return back()
                ->withErrors(['year-start' => 'Tahun ajaran ' . $newAcademicYear . ' semester ' . ucfirst($semester) . ' sudah ada'])
                ->withInput();
        }

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
        if (!Hash::check($request->password, Auth::user()->password)) {
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

    public function closeForm()
    {
        $currentAY = AcademicYear::getActiveYear();

        if (!$currentAY) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran yang aktif saat ini.']);
        }

        $availableTargets = AcademicYear::where('id', '!=', $currentAY->id)
            ->where('is_active', false)
            ->where(function ($query) use ($currentAY) {
                $query->where('year', '>', $currentAY->year);
                if ($currentAY->semester === 'ganjil') {
                    $query->orWhere(function ($sub) use ($currentAY) {
                        $sub->where('year', $currentAY->year)->where('semester', 'genap');
                    });
                }
            })
            ->orderBy('year', 'asc')
            ->orderByRaw("CASE WHEN semester = 'ganjil' THEN 1 ELSE 2 END ASC")
            ->get();

        if ($availableTargets->isEmpty()) {
            return redirect()
                ->route('academic-years.index')
                ->withErrors(['error' => 'Belum ada periode masa depan yang tersedia. Silakan buat Tahun Ajaran / Semester baru terlebih dahulu.']);
        }

        $allStudentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();

        $preview = $this->closureService->getPreview($currentAY, $availableTargets->first());

        return view('role.kesiswaan.contents.academic-year.close', [
            'currentYear' => $currentAY,
            'availableTargets' => $availableTargets,
            'preview' => $preview,
            'allStudentClasses' => $allStudentClasses,
        ]);
    }

    public function processClosure(Request $request)
    {
        $request->validate(
            [
                'password' => 'required',
                'target_id' => 'required|exists:academic_years,id',
                'class_mappings' => 'nullable|array',
                'class_mappings.*.from' => 'required|string|max:50',
                'class_mappings.*.to' => 'required|string|max:50',
            ],
            [
                'password.required' => 'Password wajib diisi untuk melanjutkan.',
                'target_id.required' => 'Tahun ajaran tujuan wajib dipilih.',
                'target_id.exists' => 'Tahun ajaran tujuan tidak valid.',
                'class_mappings.*.to.required' => 'Kelas tujuan wajib diisi untuk setiap mapping.',
                'class_mappings.*.to.max' => 'Nama kelas tujuan maksimal 50 karakter.',
            ],
        );

        if ($request->filled('class_mappings')) {
            $targets = collect($request->input('class_mappings'))->pluck('to')->map(fn($v) => strtoupper(trim($v)))->filter();

            if ($targets->count() !== $targets->unique()->count()) {
                return back()
                    ->withErrors(['class_mappings' => 'Tidak boleh ada kelas tujuan yang sama. Pastikan setiap mapping unik.'])
                    ->withInput();
            }
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah!']);
        }

        $currentAY = AcademicYear::getActiveYear();
        $targetAY = AcademicYear::findOrFail($request->target_id);

        if (!$currentAY) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        if ($targetAY->is_active) {
            return back()->withErrors(['target_id' => 'Tahun ajaran tujuan sudah aktif.']);
        }

        // Validasi kronologis
        $currentYearStart = (int) explode('/', $currentAY->year)[0];
        $targetYearStart = (int) explode('/', $targetAY->year)[0];

        if ($targetYearStart < $currentYearStart) {
            return back()->withErrors(['target_id' => 'Tahun ajaran tujuan tidak boleh lebih lama.']);
        }

        if ($targetAY->year === $currentAY->year && $targetAY->semester === $currentAY->semester) {
            return back()->withErrors(['target_id' => 'Tahun ajaran tujuan tidak boleh sama dengan yang aktif.']);
        }

        $classMappings = $request->input('class_mappings', []);
        $results = $this->closureService->close($currentAY, $targetAY, $classMappings);

        $msg = $results['type'] === 'academic_year' ? "Tahun ajaran berhasil ditutup. {$results['graduated_students']} siswa lulus, {$results['promoted_students']} naik kelas, {$results['closed_memberships']} keanggotaan diarsipkan." : "Semester berhasil ditutup. {$results['closed_memberships']} keanggotaan diarsipkan. Grade siswa tetap.";

        return redirect()
            ->route('academic-years.index')
            ->with('success', $msg . ' Silakan download Excel untuk referensi di periode baru.');
    }

    public function export(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        // Gunakan tahun ajaran yang dipilih, atau fallback ke yang sedang aktif
        $academicYear = $request->academic_year_id ? AcademicYear::find($request->academic_year_id) : AcademicYear::getActiveYear();

        // Generate filename dinamis sesuai pola
        $parts = ['Data_Siswa_Aktif'];
        if ($academicYear) {
            $parts[] = preg_replace('/[^a-zA-Z0-9]/', '_', $academicYear->year . '_' . ucfirst($academicYear->semester));
        }
        $parts[] = now()->format('YmdHis');

        $filename = implode('_', $parts) . '.xlsx';

        // Download menggunakan class Export yang sudah di-patch
        return Excel::download(new ActiveStudentExport(), $filename);
    }
}
