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
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        $activeAY = AcademicYear::getActiveYear();
        $selectedAY = $activeAY;

        if ($request->filled('academic_year_id')) {
            $selectedAY = AcademicYear::find($request->academic_year_id);
        }

        $query = ScoreSummary::with(['membership.student.studentClass', 'membership.extracurricular', 'academicYear'])
            ->when($selectedAY, fn ($q) => $q->where('academic_year_id', $selectedAY->id))
            ->when($request->filled('extracurricular_id'), fn ($q) => $q->whereHas('membership', fn ($m) => $m->where('extracurricular_id', $request->extracurricular_id)));

        $scores = $query->latest()->paginate(15);
        $extracurriculars = Extracurricular::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->orderBy('semester', 'desc')->get();
        $studentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();

        return view('role.kesiswaan.contents.score.index', compact('scores', 'extracurriculars', 'activeAY', 'selectedAY', 'academicYears', 'studentClasses'));
    }

    public function input(Request $request)
    {
        $activeAY = AcademicYear::getActiveYear();

        if (! $activeAY) {
            return redirect()->route('scores.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran aktif']);
        }

        $extracurriculars = Extracurricular::where('is_active', true)->get();
        $studentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->orderBy('semester', 'desc')->get();

        return view('role.kesiswaan.contents.score.input', compact('extracurriculars', 'activeAY', 'studentClasses', 'academicYears'));
    }

    public function getStudentsForScore(Request $request)
    {
        $extracurricularId = $request->extracurricular_id;
        $academicYearId = $request->academic_year_id;

        if (! $extracurricularId || ! $academicYearId) {
            return response()->json([]);
        }

        $memberships = ExtracurricularMembership::with(['student.studentClass'])
            ->where('extracurricular_id', $extracurricularId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', MembershipStatus::AKTIF->value)
            ->get();

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
