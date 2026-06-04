<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMembership;
use App\Models\Log;
use App\Models\Student;
use App\Models\StudentClass;
use App\Services\MembershipTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = strtolower($request->search);
                return $q->where(function ($q) use ($search) {
                    $q->whereHas('student', function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(id_number) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('extracurricular', function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    });
                });
            });

        $memberships = $query->latest()->paginate(15)->withQueryString();
        $extracurriculars = Extracurricular::where('is_active', true)->get();

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
        $user = Auth::user();
 
        $request->validate([
            'student_id'         => 'required|exists:students,id',
            'class_id'           => 'required|exists:student_classes,id',
            'extracurricular_id' => 'required|exists:extracurriculars,id',
        ]);
 
        $student  = Student::findOrFail($request->student_id);
        $activeAY = AcademicYear::getActiveYear();
 
        if (!$activeAY) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran yang aktif.']);
        }
 
        if (!$student->isEligibleForExtracurricular()) {
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
 
        $extracurricular = Extracurricular::findOrFail($request->extracurricular_id);
 
        DB::transaction(function () use ($user, $student, $extracurricular, $activeAY) {
            ExtracurricularMembership::create([
                'student_id'         => $student->id,
                'extracurricular_id' => $extracurricular->id,
                'academic_year_id'   => $activeAY->id,
                'status'             => MembershipStatus::AKTIF->value,
            ]);
 
            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Tambah anggota ekstrakurikuler',
                'detail'   => $user->name . ' mendaftarkan ' . $student->name . ' (NIS: ' . $student->id_number . ') ke ' . $extracurricular->name . ' tahun ajaran ' . $activeAY->year . ' ' . ucfirst($activeAY->semester),
            ]);
        });
 
        return redirect()->route('memberships.index')->with('success', 'Siswa berhasil didaftarkan.');
    }

    public function updateStatus(Request $request, ExtracurricularMembership $membership)
    {
        $user = Auth::user();
 
        $request->validate([
            'status' => 'required|in:aktif,selesai,drop',
        ]);
 
        $oldStatus = $membership->status;
        $newStatus = $request->status;
 
        if ($oldStatus === $newStatus) {
            return back()->with('success', 'Status keanggotaan berhasil diubah menjadi ' . $newStatus . '.');
        }
 
        DB::transaction(function () use ($user, $membership, $oldStatus, $newStatus) {
            $membership->update(['status' => $newStatus]);
 
            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Ubah status keanggotaan',
                'detail'   => $user->name . ' mengubah status keanggotaan ' . $membership->student->name . ' di ' . $membership->extracurricular->name . ' dari ' . $oldStatus . ' menjadi ' . $newStatus,
            ]);
        });
 
        return back()->with('success', 'Status keanggotaan berhasil diubah menjadi ' . $newStatus . '.');
    }

    public function destroy(ExtracurricularMembership $membership)
    {
        $user            = Auth::user();
        $studentName     = $membership->student->name;
        $studentNis      = $membership->student->id_number;
        $extracurricular = $membership->extracurricular->name;
 
        DB::transaction(function () use ($user, $membership, $studentName, $studentNis, $extracurricular) {
            if ($membership->presenceDetails()->exists() || $membership->scores()->exists()) {
                $membership->markAsDropped();
 
                Log::create([
                    'user_id'  => $user->id,
                    'activity' => 'Drop keanggotaan ekstrakurikuler',
                    'detail'   => $user->name . ' men-drop keanggotaan ' . $studentName . ' (NIS: ' . $studentNis . ') dari ' . $extracurricular . ' (data kehadiran/nilai dipertahankan)',
                ]);
            } else {
                $membership->delete();
 
                Log::create([
                    'user_id'  => $user->id,
                    'activity' => 'Hapus keanggotaan ekstrakurikuler',
                    'detail'   => $user->name . ' menghapus keanggotaan ' . $studentName . ' (NIS: ' . $studentNis . ') dari ' . $extracurricular,
                ]);
            }
        });
 
        $wasDropped = !$membership->exists || $membership->status === MembershipStatus::DROP->value;
 
        return redirect()->route('memberships.index')->with(
            'success',
            $wasDropped
                ? 'Keanggotaan ditandai sebagai drop (data kehadiran/nilai masih dipertahankan).'
                : 'Data keanggotaan dihapus.'
        );
    }
}
