<?php

namespace App\Http\Controllers;

use App\Enums\PresenceStatus;
use App\Exports\AttendanceExport;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\Presence;
use App\Models\StudentClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $activeAY = AcademicYear::getActiveYear();
        $selectedAY = $activeAY;

        if ($request->filled('academic_year_id')) {
            $selectedAY = AcademicYear::find($request->academic_year_id);
        }

        $extracurriculars = Extracurricular::where('is_active', true)->get();
        $studentClasses = StudentClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->orderBy('semester', 'desc')->get();

        return view('role.kesiswaan.contents.report.index', compact(
            'extracurriculars', 'studentClasses', 'academicYears', 'activeAY', 'selectedAY'
        ));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'academic_year_id'   => 'required|exists:academic_years,id',
            'month'              => 'required|integer|min:1|max:12',
            'class_id'           => 'nullable|exists:student_classes,id',
        ]);

        $extracurricular = Extracurricular::findOrFail($request->extracurricular_id);
        $academicYear    = AcademicYear::findOrFail($request->academic_year_id);
        $studentClass    = $request->class_id ? StudentClass::findOrFail($request->class_id) : null;
        $month           = (int) $request->month;

        $yearParts = explode('-', $academicYear->year);
        $startYear = (int) $yearParts[0];
        $endYear   = isset($yearParts[1]) ? (int) $yearParts[1] : $startYear + 1;

        $queryYear = $month >= 7 ? $startYear : $endYear;
        $monthName = $this->getMonthName($month);

        $presences = Presence::with(['details.student.studentClass'])
            ->where('extracurricular_id', $request->extracurricular_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $queryYear)
            ->orderBy('date')
            ->get();

        $totalSessions = count($presences);

        $students = collect();
        foreach ($presences as $presence) {
            foreach ($presence->details as $detail) {
                $student = $detail->student;
                if ($student) {
                    if ($studentClass && $student->studentClass?->id != $studentClass->id) {
                        continue;
                    }
                    if (! $students->contains('id', $student->id)) {
                        $students->push($student);
                    }
                }
            }
        }
        $students = $students->sortBy('name')->values();

        $stats = [];
        foreach ($students as $student) {
            $present = $sick = $permission = $absent = 0;
            foreach ($presences as $presence) {
                $detail = $presence->details->firstWhere('student_id', $student->id);
                if ($detail) {
                    match ($detail->status) {
                        PresenceStatus::HADIR => $present++,
                        PresenceStatus::SAKIT => $sick++,
                        PresenceStatus::IZIN  => $permission++,
                        PresenceStatus::ALPHA => $absent++,
                    };
                }
            }
            $percentage = $totalSessions > 0 ? round(($present / $totalSessions) * 100, 1) : 0;

            $stats[$student->id] = [
                'present'    => $present,
                'sick'       => $sick,
                'permission' => $permission,
                'absent'     => $absent,
                'percentage' => $percentage,
            ];
        }

        return view('role.kesiswaan.contents.report.preview', compact(
            'extracurricular', 'academicYear', 'presences', 'students', 'stats', 'month', 'monthName'
        ));
    }

    private function getMonthName(int $month): string
    {
        return [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month];
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'month' => 'required|integer|min:1|max:12',
            'class_id' => 'nullable|exists:student_classes,id',
        ]);

        $extracurricular = Extracurricular::findOrFail($request->extracurricular_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $month = (int) $request->month;
        $studentClass = $request->class_id ? StudentClass::findOrFail($request->class_id) : null;

        $yearParts = explode('-', $academicYear->year);
        $startYear = (int) $yearParts[0];
        $endYear = isset($yearParts[1]) ? (int) $yearParts[1] : $startYear + 1;

        $queryYear = $month >= 7 ? $startYear : $endYear;
        $year = $queryYear;
        $monthName = $this->getMonthName($month);

        $presences = Presence::with(['details.student.studentClass'])
            ->where('extracurricular_id', $request->extracurricular_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $queryYear)
            ->orderBy('date')
            ->get();

        $students = collect();
        foreach ($presences as $presence) {
            foreach ($presence->details as $detail) {
                $student = $detail->student;
                if ($student) {
                    if ($studentClass && $student->studentClass?->id != $studentClass->id) {
                        continue;
                    }
                    if (! $students->contains('id', $student->id)) {
                        $students->push($student);
                    }
                }
            }
        }
        $students = $students->sortBy('name')->values();

        $rows = [];
        $rows[] = ['LAPORAN KEHADIRAN SISWA'];
        $rows[] = ['Ekstrakurikuler: '.$extracurricular->name];
        $rows[] = ['Bulan: '.$monthName.' '.$year];
        $rows[] = ['Tahun Ajaran: '.$academicYear->display_name];
        if ($studentClass) {
            $rows[] = ['Kelas: '.$studentClass->name];
        }
        $rows[] = ['', '', '', '', '', '', '', '', ''];

        $header = ['', 'NIS', 'Nama Siswa', 'Kelas'];
        foreach ($presences as $p) {
            $header[] = Carbon::parse($p->date)->format('d');
        }
        $header[] = 'Hadir';
        $header[] = 'Sakit';
        $header[] = 'Izin';
        $header[] = 'Alpha';
        $header[] = 'Persentase';
        $rows[] = $header;

        $no = 1;
        foreach ($students as $student) {
            $row = [$no++, $student->id_number ?? '-', $student->name ?? '-', $student->studentClass?->name ?? '-'];
            $present = $sick = $permission = $absent = 0;
            foreach ($presences as $presence) {
                $detail = $presence->details->firstWhere('student_id', $student->id);
                if ($detail) {
                    match ($detail->status) {
                        PresenceStatus::HADIR => $present++,
                        PresenceStatus::SAKIT => $sick++,
                        PresenceStatus::IZIN => $permission++,
                        PresenceStatus::ALPHA => $absent++,
                    };
                    $row[] = match ($detail->status) {
                        PresenceStatus::HADIR => 'H', PresenceStatus::SAKIT => 'S', PresenceStatus::IZIN => 'I', PresenceStatus::ALPHA => 'A',
                    };
                } else {
                    $row[] = '-';
                }
            }
            $totalSessions = count($presences);
            $percentage = $totalSessions > 0 ? round(($present / $totalSessions) * 100, 1) : 0;
            $row[] = $present;
            $row[] = $sick;
            $row[] = $permission;
            $row[] = $absent;
            $row[] = $percentage.'%';
            $rows[] = $row;
        }

        $parts = ['Kehadiran', preg_replace('/[^a-zA-Z0-9]/', '_', $extracurricular->name), $monthName];
        if ($studentClass) {
            $parts[] = preg_replace('/[^a-zA-Z0-9]/', '_', $studentClass->name);
        }

        $parts[] = $year;
        $parts[] = now()->format('YmdHis');
        $filename = implode('_', $parts).'.xlsx';

        return Excel::download(new AttendanceExport($rows), $filename);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'month' => 'required|integer|min:1|max:12',
            'class_id' => 'nullable|exists:student_classes,id',
        ]);

        $extracurricular = Extracurricular::findOrFail($request->extracurricular_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $studentClass = $request->class_id
            ? StudentClass::find($request->class_id)
            : null;
        $month = (int) $request->month;

        $yearParts = explode('-', $academicYear->year);
        $startYear = (int) $yearParts[0];
        $endYear = isset($yearParts[1]) ? (int) $yearParts[1] : $startYear + 1;

        $queryYear = $month >= 7 ? $startYear : $endYear;
        $year = $queryYear;
        $monthName = $this->getMonthName($month);

        $presences = Presence::with(['details.student.studentClass'])
            ->where('extracurricular_id', $request->extracurricular_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $queryYear)
            ->orderBy('date')
            ->get();

        $students = collect();
        foreach ($presences as $presence) {
            foreach ($presence->details as $detail) {
                $student = $detail->student;
                if ($student) {
                    if ($studentClass && $student->studentClass?->id != $studentClass->id) {
                        continue;
                    }
                    if (! $students->contains('id', $student->id)) {
                        $students->push($student);
                    }
                }
            }
        }
        $students = $students->sortBy('name')->values();

        $stats = [];
        $detailsData = [];
        foreach ($students as $student) {
            $present = $sick = $permission = $absent = 0;
            $presenceDetails = [];
            foreach ($presences as $presence) {
                $detail = $presence->details->firstWhere('student_id', $student->id);
                if ($detail) {
                    match ($detail->status) {
                        PresenceStatus::HADIR => $present++,
                        PresenceStatus::SAKIT => $sick++,
                        PresenceStatus::IZIN => $permission++,
                        PresenceStatus::ALPHA => $absent++,
                    };
                    $presenceDetails[] = match ($detail->status) {
                        PresenceStatus::HADIR => 'H', PresenceStatus::SAKIT => 'S', PresenceStatus::IZIN => 'I', PresenceStatus::ALPHA => 'A',
                    };
                } else {
                    $presenceDetails[] = '-';
                }
            }
            $totalSessions = count($presences);
            $percentage = $totalSessions > 0 ? round(($present / $totalSessions) * 100, 1) : 0;
            $stats[$student->id] = [
                'present' => $present,
                'sick' => $sick,
                'permission' => $permission,
                'absent' => $absent,
                'percentage' => $percentage,
            ];
            $detailsData[$student->id] = $presenceDetails;
        }

        $parts = ['Kehadiran', preg_replace('/[^a-zA-Z0-9]/', '_', $extracurricular->name), $monthName];
        if ($studentClass) {
            $parts[] = preg_replace('/[^a-zA-Z0-9]/', '_', $studentClass->name);
        }
        $parts[] = $year;
        $parts[] = now()->format('YmdHis');
        $filename = implode('_', $parts).'.pdf';

        $pdf = Pdf::loadView('role.kesiswaan.contents.report.pdf', compact(
            'extracurricular',
            'academicYear',
            'presences',
            'students',
            'stats',
            'detailsData',
            'monthName',
            'year',
            'studentClass'
        ));

        return $pdf->download($filename);
    }
}
