<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMembership;
use App\Models\Log;
use App\Models\Presence;
use App\Models\PresenceDetail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Extracurricular::query();

        if ($user->role === 'pembina') {
            $query->whereHas('users', fn($q) => $q->where('user_id', $user->id));
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        });

        $x['extracurriculars'] = $query->orderBy('name')->paginate(10)->withQueryString();

        $view = match ($user->role) {
            'kesiswaan' => 'role.kesiswaan.contents.presence.index',
            'admin' => 'role.admin.contents.presence.index',
            'pembina' => 'role.pembina.contents.presence.index',
            default => abort(403),
        };

        return view($view, $x);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $extracurricular = Extracurricular::findOrFail($id);

        if ($user->role === 'pembina') {
            if (!$extracurricular->users()->where('user_id', $user->id)->exists()) {
                abort(403);
            }
        }

        $activeAY = AcademicYear::getActiveYear();
        $selectedAY = $activeAY;

        if ($request->filled('academic_year_id')) {
            $selectedAY = AcademicYear::find($request->academic_year_id);
        }

        $query = Presence::with(['academicYear', 'details'])->where('extracurricular_id', $id);

        if ($selectedAY) {
            $query->where('academic_year_id', $selectedAY->id);
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(date) LIKE ?', ["%{$search}%"]);
            });
        });

        $x['extracurricular'] = $extracurricular;
        $x['presences'] = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();
        $x['selectedAY'] = $selectedAY;
        $x['academicYears'] = AcademicYear::orderByRaw(
            "
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
        ",
        )->get();

        $view = match ($user->role) {
            'kesiswaan' => 'role.kesiswaan.contents.presence.show',
            'admin' => 'role.admin.contents.presence.show',
            'pembina' => 'role.pembina.contents.presence.show',
            default => abort(403),
        };

        return view($view, $x);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $extracurricularId = $request->extracurricular_id;
        $extracurricular = Extracurricular::findOrFail($extracurricularId);

        if ($user->role === 'pembina') {
            if (!$extracurricular->users()->where('user_id', $user->id)->exists()) {
                abort(403);
            }
        }

        $activeAY = AcademicYear::getActiveYear();

        if (!$activeAY) {
            return redirect()
                ->route('presence.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        $x['extracurricular'] = $extracurricular;
        $x['students'] = Student::query()
            ->whereHas('memberships', function ($query) use ($extracurricularId, $activeAY) {
                $query->where('extracurricular_id', $extracurricularId)->where('academic_year_id', $activeAY->id)->where('status', 'aktif');
            })
            ->with([
                'studentClass',
                'memberships' => function ($query) use ($extracurricularId, $activeAY) {
                    $query->where('extracurricular_id', $extracurricularId)->where('academic_year_id', $activeAY->id);
                },
            ])
            ->leftJoin('student_classes', 'students.class_id', '=', 'student_classes.id')
            ->select('students.*')
            ->orderBy('students.grade')
            ->orderBy('student_classes.name')
            ->orderBy('students.name')
            ->get();

        foreach ($x['students'] as $student) {
            $student->is_class_inactive = !$student->studentClass || !$student->studentClass->is_active;
        }

        $view = match ($user->role) {
            'kesiswaan' => 'role.kesiswaan.contents.presence.create',
            'admin' => 'role.admin.contents.presence.create',
            'pembina' => 'role.pembina.contents.presence.create',
            default => abort(403),
        };

        return view($view, $x);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,sick,permission,absent',
        ]);

        $exists = Presence::where('extracurricular_id', $validated['extracurricular_id'])->whereDate('date', $validated['date'])->exists();

        if ($exists) {
            return back()
                ->withErrors(['date' => 'Presensi untuk tanggal ini sudah ada!'])
                ->withInput();
        }

        $activeAY = AcademicYear::getActiveYear();

        if (!$activeAY) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        $extracurricular = Extracurricular::findOrFail($validated['extracurricular_id']);

        DB::transaction(function () use ($user, $validated, $activeAY, $extracurricular) {
            $presence = Presence::create([
                'extracurricular_id' => $validated['extracurricular_id'],
                'academic_year_id' => $activeAY->id,
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['attendance'] as $studentId => $status) {
                $membership = ExtracurricularMembership::where('student_id', $studentId)->where('extracurricular_id', $validated['extracurricular_id'])->where('academic_year_id', $activeAY->id)->first();

                if ($membership) {
                    PresenceDetail::create([
                        'presence_id' => $presence->id,
                        'membership_id' => $membership->id,
                        'student_id' => $studentId,
                        'status' => $status,
                    ]);
                }
            }

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Tambah presensi',
                'detail' => $user->name . ' menambahkan presensi ' . $extracurricular->name . ' tanggal ' . $validated['date'] . ' (' . count($validated['attendance']) . ' siswa)',
            ]);
        });

        return redirect()->route('presence.show', $validated['extracurricular_id'])->with('success', 'Presensi berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $x['presence'] = Presence::with([
            'extracurricular',
            'details' => function ($query) {
                $query->join('students', 'presence_details.student_id', '=', 'students.id')->leftJoin('student_classes', 'students.class_id', '=', 'student_classes.id')->select('presence_details.*')->orderBy('students.grade')->orderBy('student_classes.name')->orderBy('students.name');
            },
            'details.student.studentClass',
        ])->findOrFail($id);

        $x['extracurricular'] = $x['presence']->extracurricular;

        if ($user->role === 'pembina') {
            if (!$x['extracurricular']->users()->where('user_id', $user->id)->exists()) {
                abort(403);
            }
        }

        $view = match ($user->role) {
            'kesiswaan' => 'role.kesiswaan.contents.presence.edit',
            'admin' => 'role.admin.contents.presence.edit',
            'pembina' => 'role.pembina.contents.presence.edit',
            default => abort(403),
        };

        return view($view, $x);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $presence = Presence::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,sick,permission,absent',
        ]);

        $oldDate = $presence->date->toDateString();
        $activeAY = $presence->academicYear;

        DB::transaction(function () use ($user, $presence, $validated, $activeAY, $oldDate) {
            $presence->update([
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['attendance'] as $studentId => $status) {
                $membership = ExtracurricularMembership::where('student_id', $studentId)->where('extracurricular_id', $presence->extracurricular_id)->where('academic_year_id', $activeAY->id)->first();

                $detailData = [
                    'presence_id' => $presence->id,
                    'student_id' => $studentId,
                    'status' => $status,
                ];

                if ($membership) {
                    $detailData['membership_id'] = $membership->id;
                }

                PresenceDetail::updateOrCreate(['presence_id' => $presence->id, 'student_id' => $studentId], $detailData);
            }

            $logDetail = $oldDate !== $validated['date'] ? $user->name . ' memperbarui presensi ' . $presence->extracurricular->name . ' dari tanggal ' . $oldDate . ' menjadi ' . $validated['date'] : $user->name . ' memperbarui presensi ' . $presence->extracurricular->name . ' tanggal ' . $validated['date'];

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Ubah presensi',
                'detail' => $logDetail,
            ]);
        });

        return redirect()->route('presence.show', $presence->extracurricular_id)->with('success', 'Presensi berhasil diperbarui!');
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $presence = Presence::with('extracurricular')->findOrFail($id);

        $request->validate(['password' => 'required']);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah!']);
        }

        $extracurricularId = $presence->extracurricular_id;
        $extracurricularName = $presence->extracurricular->name;
        $date = $presence->date->toDateString();

        DB::transaction(function () use ($user, $presence, $extracurricularName, $date) {
            $presence->delete(); // cascade ke presence_details jika diset di migration

            Log::create([
                'user_id' => $user->id,
                'activity' => 'Hapus presensi',
                'detail' => $user->name . ' menghapus presensi ' . $extracurricularName . ' tanggal ' . $date,
            ]);
        });

        return redirect()->route('presence.show', $extracurricularId)->with('success', 'Presensi berhasil dihapus!');
    }
}
