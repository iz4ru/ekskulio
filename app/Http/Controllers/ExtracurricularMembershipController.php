<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMembership;
use App\Models\Student;
use App\Models\StudentClass;
use App\Services\MembershipTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtracurricularMembershipController extends Controller
{
    public function __construct(
        private MembershipTransitionService $membershipService
    ) {}

    public function index(Request $request)
    {
        $activeAY = AcademicYear::getActiveYear();
        $selectedAY = $activeAY;

        if ($request->filled('academic_year_id')) {
            $selectedAY = AcademicYear::find($request->academic_year_id);
        }

        $query = ExtracurricularMembership::with(['student.studentClass', 'extracurricular', 'academicYear'])
            ->when($selectedAY, function ($q) use ($selectedAY) {
                return $q->byAcademicYear($selectedAY->id);
            })
            ->when($request->filled('extracurricular_id'), function ($q) use ($request) {
                return $q->byExtracurricular($request->extracurricular_id);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                return $q->where('status', $request->status);
            });

        $memberships = $query->latest()->paginate(15);
        $extracurriculars = Extracurricular::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->orderBy('semester', 'desc')->get();

        return view('role.kesiswaan.contents.membership.index', compact('memberships', 'extracurriculars', 'activeAY', 'selectedAY', 'academicYears'));
    }

    public function create()
    {
        $activeAY = AcademicYear::getActiveYear();

        if (! $activeAY) {
            return redirect()->route('memberships.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran yang aktif.']);
        }

        $extracurriculars = Extracurricular::where('is_active', true)->get();
        $studentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();

        return view('role.kesiswaan.contents.membership.create', compact('extracurriculars', 'activeAY', 'studentClasses'));
    }

    public function getEligibleStudents(Request $request)
    {
        $classId = $request->class_id;

        if (! $classId) {
            return response()->json([]);
        }

        $activeAY = AcademicYear::getActiveYear();
        if (! $activeAY) {
            return response()->json([]);
        }

        $students = Student::query()
            ->where('class_id', $classId)
            ->where('status', StudentStatus::AKTIF->value)
            ->where('grade', '!=', StudentGrade::XII->value)
            ->whereDoesntHave('memberships', function ($query) use ($activeAY) {
                $query->byAcademicYear($activeAY->id);
            })
            ->select('id', 'name', 'id_number', 'grade')
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:student_classes,id',
            'extracurricular_id' => 'required|exists:extracurriculars,id',
        ]);

        $student = Student::findOrFail($request->student_id);
        $activeAY = AcademicYear::getActiveYear();

        if (! $activeAY) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran yang aktif.']);
        }

        if (! $student->isEligibleForExtracurricular()) {
            $reason = $student->grade === StudentGrade::XII->value
                ? 'Siswa Kelas XII tidak diperkenankan mendaftar ekstrakurikuler.'
                : 'Status siswa tidak aktif.';

            return back()->withErrors(['student_id' => $reason]);
        }

        if ($this->membershipService->checkDuplicateEnrollment(
            $student->id,
            $request->extracurricular_id,
            $activeAY->id
        )) {
            return back()->withErrors(['extracurricular_id' => 'Siswa sudah terdaftar di ekstrakurikuler ini pada tahun ajaran aktif.']);
        }

        $hasOtherActive = ExtracurricularMembership::where('student_id', $student->id)
            ->byAcademicYear($activeAY->id)
            ->active()
            ->exists();

        if ($hasOtherActive) {
            return back()->withErrors(['extracurricular_id' => 'Siswa sudah terdaftar di ekstrakurikuler lain yang masih aktif.']);
        }

        DB::beginTransaction();
        try {
            ExtracurricularMembership::create([
                'student_id' => $student->id,
                'extracurricular_id' => $request->extracurricular_id,
                'academic_year_id' => $activeAY->id,
                'status' => MembershipStatus::AKTIF->value,
            ]);

            DB::commit();

            return redirect()->route('memberships.index')->with('success', 'Siswa berhasil didaftarkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mendaftarkan siswa: '.$e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, ExtracurricularMembership $membership)
    {
        $request->validate([
            'status' => 'required|in:aktif,selesai,drop',
        ]);

        $membership->update(['status' => $request->status]);

        return back()->with('success', 'Status keanggotaan berhasil diubah menjadi '.$request->status.'.');
    }

    public function destroy(ExtracurricularMembership $membership)
    {
        if ($membership->presenceDetails()->exists() || $membership->scores()->exists()) {
            $membership->markAsDropped();

            return redirect()->route('memberships.index')
                ->with('success', 'Keanggotaan ditandai sebagai drop (data kehadiran/nilai masih dipertahankan).');
        }

        $membership->delete();

        return redirect()->route('memberships.index')->with('success', 'Data keanggotaan dihapus.');
    }
}
