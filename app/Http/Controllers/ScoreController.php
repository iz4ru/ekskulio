<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Exports\ScoreExport;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMembership;
use App\Models\ScoreSummary;
use App\Models\StudentClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeAY = AcademicYear::getActiveYear();
        $selectedAY = $activeAY;

        if ($request->filled('academic_year_id')) {
            $selectedAY = AcademicYear::find($request->academic_year_id);
        }

        $query = ScoreSummary::with(['membership.student.studentClass', 'membership.extracurricular', 'academicYear'])
            ->when($selectedAY, fn ($q) => $q->where('academic_year_id', $selectedAY->id))
            ->when($request->filled('extracurricular_id'), fn ($q) => $q->whereHas('membership', fn ($m) => $m->where('extracurricular_id', $request->extracurricular_id)))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = strtolower($request->search);
                return $q->where(function ($q) use ($search) {
                    $q->whereHas('membership', function ($m) use ($search) {
                        $m->whereHas('student', function ($s) use ($search) {
                            $s->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(id_number) LIKE ?', ["%{$search}%"]);
                        })
                        ->orWhereHas('extracurricular', function ($e) use ($search) {
                            $e->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        });
                    });
                });
            });

        // Pembina hanya bisa melihat nilai dari ekskul yang mereka bina
        if ($user->role === 'pembina') {
            $query->whereHas('membership.extracurricular.users', fn($q) => $q->where('user_id', $user->id));
        }

        $scores = $query->latest()->paginate(15)->withQueryString();
        
        // Pembina hanya melihat ekskul mereka sendiri di dropdown
        $extracurricularsQuery = Extracurricular::where('is_active', true);
        if ($user->role === 'pembina') {
            $extracurricularsQuery->whereHas('users', fn($q) => $q->where('user_id', $user->id));
        }
        $extracurriculars = $extracurricularsQuery->get();

        $isAdvisorSingleExtracurricular = $user->role === 'pembina' && $extracurriculars->count() === 1;
        
        $academicYears = AcademicYear::orderByRaw("
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
        ")->get();

        $studentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();

        $view = match (true) {
            $user->role === 'kesiswaan' => 'role.kesiswaan.contents.score.index',
            $user->role === 'pembina' => 'role.pembina.contents.score.index',
            default => abort(403),
        };

        return view($view, compact(
            'scores', 'extracurriculars', 'activeAY', 'selectedAY', 
            'academicYears', 'studentClasses', 'isAdvisorSingleExtracurricular'
        ));
    }

    public function input(Request $request)
    {
        $user = Auth::user();
        $activeAY = AcademicYear::getActiveYear();

        if (! $activeAY) {
            return redirect()->route('scores.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran aktif']);
        }

        // Pembina hanya melihat ekskul mereka sendiri di dropdown
        $extracurricularsQuery = Extracurricular::where('is_active', true);
        if ($user->role === 'pembina') {
            $extracurricularsQuery->whereHas('users', fn($q) => $q->where('user_id', $user->id));
        }
        $extracurriculars = $extracurricularsQuery->get();

        $studentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();

        $academicYears = AcademicYear::orderByRaw("
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
        ")->get();

        $view = match (true) {
            $user->role === 'kesiswaan' => 'role.kesiswaan.contents.score.input',
            $user->role === 'pembina' => 'role.pembina.contents.score.input',
            default => abort(403),
        };

        return view($view, compact('extracurriculars', 'activeAY', 'studentClasses', 'academicYears'));
    }

    public function getStudents(Request $request)
    {
        $extracurricularId = $request->extracurricular_id;
        $academicYearId = $request->academic_year_id;
        $classId = $request->class_id; // TAMBAHAN

        if (! $extracurricularId || ! $academicYearId) {
            return response()->json([]);
        }

        $query = ExtracurricularMembership::with(['student.studentClass'])
            ->where('extracurricular_id', $extracurricularId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', MembershipStatus::AKTIF->value);

        // TAMBAHAN: Filter by class_id jika ada
        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $memberships = $query->get();

        $existingScores = ScoreSummary::where('academic_year_id', $academicYearId)
            ->whereIn('membership_id', $memberships->pluck('id'))
            ->get()
            ->keyBy('membership_id');

        $data = $memberships->map(function ($m) use ($existingScores) {
            $student = $m->student;
            $scoreData = $existingScores[$m->id] ?? null;

            return [
                'membership_id' => $m->id,
                'student_id' => $student?->id,
                'id_number' => $student?->id_number ?? '-',
                'name' => $student?->name ?? '-',
                'class' => $student?->studentClass?->name ?? '-',
                'score' => $scoreData?->score ?? null,
                'notes' => $scoreData?->notes ?? '',
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'scores' => 'required|array',
            'academic_year_id' => 'required|exists:academic_years,id',
        ], [
            'scores.required' => 'Data nilai wajib diisi',
        ]);

        $activeAY = AcademicYear::findOrFail($request->academic_year_id);

        DB::beginTransaction();
        try {
            foreach ($request->scores as $membershipId => $scoreData) {
                if (empty($scoreData['score']) && empty($scoreData['notes'])) {
                    continue;
                }

                $score = $scoreData['score'] ?? 0;
                $predicate = $score > 0 ? ScoreSummary::getPredicateFromScore($score) : null;

                ScoreSummary::updateOrCreate(
                    [
                        'membership_id' => $membershipId,
                        'academic_year_id' => $activeAY->id,
                    ],
                    [
                        'score' => $score,
                        'predicate' => $predicate,
                        'notes' => $scoreData['notes'] ?? null,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('scores.index')->with('success', 'Nilai berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menyimpan nilai: '.$e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'type' => 'sometimes|in:excel,pdf',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'extracurricular_id' => 'nullable|exists:extracurriculars,id',
            'class_id' => 'nullable|exists:student_classes,id',
        ]);

        $type = $request->type ?? 'excel';
        $academicYear = $request->academic_year_id ? AcademicYear::find($request->academic_year_id) : AcademicYear::getActiveYear();
        $extracurricular = $request->extracurricular_id ? Extracurricular::find($request->extracurricular_id) : null;
        $studentClass = $request->class_id ? StudentClass::find($request->class_id) : null;

        $query = ScoreSummary::with(['membership.student.studentClass', 'membership.extracurricular', 'academicYear'])
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->when($extracurricular, fn ($q) => $q->whereHas('membership', fn ($m) => $m->where('extracurricular_id', $extracurricular->id)))
            ->when($studentClass, fn ($q) => $q->whereHas('membership.student', fn ($s) => $s->where('class_id', $studentClass->id)));

        if ($user->role === 'pembina') {
            $query->whereHas('membership.extracurricular.users', fn($q) => $q->where('user_id', $user->id));
        }

        $scores = $query->get()->sortBy('membership.student.name')->values();

        $parts = ['Nilai_Siswa'];
        if ($extracurricular) {
            $parts[] = preg_replace('/[^a-zA-Z0-9]/', '_', $extracurricular->name);
        }
        if ($academicYear) {
            $parts[] = preg_replace('/[^a-zA-Z0-9]/', '_', $academicYear->year.'_'.ucfirst($academicYear->semester));
        }
        if ($studentClass) {
            $parts[] = preg_replace('/[^a-zA-Z0-9]/', '_', $studentClass->name);
        }
        $parts[] = now()->format('YmdHis');
        $filename = implode('_', $parts);

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('role.kesiswaan.contents.score.pdf', compact(
                'scores', 'academicYear', 'extracurricular', 'studentClass'
            ));

            return $pdf->download($filename.'.pdf');
        }

        $rows = [];
        $rows[] = ['LAPORAN NILAI SISWA'];
        $rows[] = ['Tahun Ajaran: '.($academicYear?->display_name ?? 'Semua')];
        $rows[] = ['Ekstrakurikuler: '.($extracurricular?->name ?? 'Semua')];
        $rows[] = ['Kelas: '.($studentClass?->name ?? 'Semua')];
        $rows[] = ['', '', '', '', '', '', '', '', ''];

        $header = ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Ekstrakurikuler', 'Tahun Ajaran', 'Nilai', 'Predikat', 'Catatan'];
        $rows[] = $header;

        $no = 1;
        foreach ($scores as $score) {
            $rows[] = [
                $no++,
                $score->membership->student->id_number ?? '-',
                $score->membership->student->name ?? '-',
                $score->membership->student->studentClass?->name ?? '-',
                $score->membership->extracurricular->name ?? '-',
                $score->academicYear->display_name ?? '-',
                $score->score ?? '-',
                $score->predicate ?? '-',
                $score->notes ?? '-',
            ];
        }

        $filename = $filename.'.xlsx';

        return Excel::download(new ScoreExport($rows), $filename);
    }
}
