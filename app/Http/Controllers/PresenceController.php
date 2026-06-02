<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMembership;
use App\Models\Presence;
use App\Models\PresenceDetail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Extracurricular::query();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        });

        $x['extracurriculars'] = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('role.kesiswaan.contents.presence.index', $x);
    }

    public function show(Request $request, $id)
    {
        $x['extracurricular'] = Extracurricular::findOrFail($id);
        $activeAY = AcademicYear::getActiveYear();
        $selectedAY = $activeAY;

        if ($request->filled('academic_year_id')) {
            $selectedAY = AcademicYear::find($request->academic_year_id);
        }

        $query = Presence::with(['academicYear', 'details'])
            ->where('extracurricular_id', $id);

        if ($selectedAY) {
            $query->where('academic_year_id', $selectedAY->id);
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(date) LIKE ?', ["%{$search}%"]);
            });
        });

        $x['presences'] = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();
        $x['selectedAY'] = $selectedAY;

        $x['academicYears'] = AcademicYear::orderByRaw("
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

        return view('role.kesiswaan.contents.presence.show', $x);
    }

    public function create(Request $request)
    {
        $extracurricularId = $request->extracurricular_id;
        $activeAY = AcademicYear::getActiveYear();

        $x['extracurricular'] = Extracurricular::findOrFail($extracurricularId);

        if (! $activeAY) {
            return redirect()->route('presence.index')
                ->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        $x['students'] = Student::query()
            ->whereHas('memberships', function ($query) use ($extracurricularId, $activeAY) {
                $query->where('extracurricular_id', $extracurricularId)
                    ->where('academic_year_id', $activeAY->id)
                    ->where('status', 'aktif');
            })
            ->with(['studentClass', 'memberships' => function ($query) use ($extracurricularId, $activeAY) {
                $query->where('extracurricular_id', $extracurricularId)
                    ->where('academic_year_id', $activeAY->id);
            }])
            ->orderBy('name')
            ->get();

        foreach ($x['students'] as $student) {
            $student->is_class_inactive = ! $student->studentClass || ! $student->studentClass->is_active;
        }

        return view('role.kesiswaan.contents.presence.create', $x);
    }

    public function store(Request $request)
    {
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

        if (! $activeAY) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        $presence = Presence::create([
            'extracurricular_id' => $validated['extracurricular_id'],
            'academic_year_id' => $activeAY->id,
            'date' => $validated['date'],
            'notes' => $validated['notes'],
        ]);

        foreach ($validated['attendance'] as $studentId => $status) {
            $membership = ExtracurricularMembership::where('student_id', $studentId)
                ->where('extracurricular_id', $validated['extracurricular_id'])
                ->where('academic_year_id', $activeAY->id)
                ->first();

            if ($membership) {
                PresenceDetail::create([
                    'presence_id' => $presence->id,
                    'membership_id' => $membership->id,
                    'student_id' => $studentId,
                    'status' => $status,
                ]);
            }
        }

        return redirect()->route('presence.show', $validated['extracurricular_id'])->with('success', 'Presensi berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $x['presence'] = Presence::with(['extracurricular', 'details.student'])->findOrFail($id);
        $x['extracurricular'] = $x['presence']->extracurricular;

        return view('role.kesiswaan.contents.presence.edit', $x);
    }

    public function update(Request $request, $id)
    {
        $presence = Presence::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,sick,permission,absent',
        ]);

        $presence->update([
            'date' => $validated['date'],
            'notes' => $validated['notes'],
        ]);

        $activeAY = $presence->academicYear;

        foreach ($validated['attendance'] as $studentId => $status) {
            $membership = ExtracurricularMembership::where('student_id', $studentId)
                ->where('extracurricular_id', $presence->extracurricular_id)
                ->where('academic_year_id', $activeAY->id)
                ->first();

            $detailData = [
                'presence_id' => $presence->id,
                'student_id' => $studentId,
                'status' => $status,
            ];

            if ($membership) {
                $detailData['membership_id'] = $membership->id;
            }

            PresenceDetail::updateOrCreate(
                [
                    'presence_id' => $presence->id,
                    'student_id' => $studentId,
                ],
                $detailData,
            );
        }

        return redirect()->route('presence.show', $presence->extracurricular_id)->with('success', 'Presensi berhasil diperbarui!');
    }

    public function destroy(Request $request, $id)
    {
        $presence = Presence::findOrFail($id);

        $request->validate(['password' => 'required']);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password salah!']);
        }

        $presence->delete();

        return redirect()->route('presence.show', $presence->extracurricular_id)->with('success', 'Presensi berhasil dihapus!');
    }
}
