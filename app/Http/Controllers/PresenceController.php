<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use App\Models\PresenceDetail;
use App\Models\Extracurricular;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PresenceController extends Controller
{
    public function index()
    {
        $x['extracurriculars'] = Extracurricular::orderBy('name')->get();
        $x['studentClasses'] = StudentClass::orderBy('name')->get();

        return view('role.kesiswaan.contents.presence.index', $x);
    }

    public function show($id)
    {
        $x['extracurricular'] = Extracurricular::findOrFail($id);

        // ✅ Ambil data presence (per pertemuan)
        $x['presences'] = Presence::with(['academicYear', 'details'])
            ->where('extracurricular_id', $id)
            ->orderBy('date', 'desc')
            ->get();

        return view('role.kesiswaan.contents.presence.show', $x);
    }

    public function create(Request $request)
    {
        $extracurricularId = $request->extracurricular_id;

        $x['extracurricular'] = Extracurricular::with('students')->findOrFail($extracurricularId);
        $x['students'] = $x['extracurricular']->students->sortBy('name, desc');

        // if student_class = is_active = false, maka row siswa tersebut di disable
        foreach ($x['students'] as $student) {
            if (!$student->studentClass->is_active) {
                $student->is_class_inactive = true;
            } else {
                $student->is_class_inactive = false;
            }
        }

        return view('role.kesiswaan.contents.presence.create', $x);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'date' => 'required|date',
            // ❌ HAPUS: 'day' => 'required|string',
            'notes' => 'nullable|string',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,sick,permission,absent',
        ]);

        // Cek duplikat
        $exists = Presence::where('extracurricular_id', $validated['extracurricular_id'])->whereDate('date', $validated['date'])->exists();

        if ($exists) {
            return back()
                ->withErrors(['date' => 'Presensi untuk tanggal ini sudah ada!'])
                ->withInput();
        }

        // ✅ Buat presence (day otomatis dari attribute)
        $presence = Presence::create([
            'extracurricular_id' => $validated['extracurricular_id'],
            'academic_year_id' => AcademicYear::getActiveYear()->id,
            'date' => $validated['date'],
            'notes' => $validated['notes'],
        ]);

        // Simpan detail per siswa
        foreach ($validated['attendance'] as $studentId => $status) {
            PresenceDetail::create([
                'presence_id' => $presence->id,
                'student_id' => $studentId,
                'status' => $status,
            ]);
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
            // ❌ HAPUS: 'day' => 'required|string',
            'notes' => 'nullable|string',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,sick,permission,absent',
        ]);

        $presence->update([
            'date' => $validated['date'],
            'notes' => $validated['notes'],
        ]);

        // Update detail
        foreach ($validated['attendance'] as $studentId => $status) {
            PresenceDetail::updateOrCreate(
                [
                    'presence_id' => $presence->id,
                    'student_id' => $studentId,
                ],
                ['status' => $status],
            );
        }

        return redirect()->route('presence.show', $presence->extracurricular_id)->with('success', 'Presensi berhasil diperbarui!');
    }

    public function destroy(Request $request, $id)
    {
        $presence = Presence::findOrFail($id);

        // Validasi password
        $request->validate(['password' => 'required']);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password salah!']);
        }

        $presence->delete();

        return redirect()->route('presence.show', $presence->extracurricular_id)->with('success', 'Presensi berhasil dihapus!');
    }
}
