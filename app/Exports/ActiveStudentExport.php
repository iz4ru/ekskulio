<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Export data siswa aktif untuk referensi/backup.
 */
class ActiveStudentExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new StudentDataSheet(),
        ];
    }
}

/**
 * Sheet 1: Data Siswa Aktif
 */
class StudentDataSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        $activeAY = AcademicYear::getActiveYear();
        
        return Student::where('status', 'aktif')
            ->with([
                'studentClass',
                'memberships' => function ($query) use ($activeAY) {
                    if ($activeAY) {
                        $query->where('academic_year_id', $activeAY->id)
                              ->where('status', 'aktif')
                              ->with('extracurricular');
                    }
                }
            ])
            ->orderBy('grade')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'nis',
            'nama_lengkap',
            'kelas',
            'tahun_masuk',
            'tingkat',
            'kode_ekstrakurikuler',
            'penghargaan',
        ];
    }

    public function map($student): array
    {
        $activeAY = AcademicYear::getActiveYear();
        $membershipCode = '-';
        
        if ($activeAY) {
            $membership = $student->memberships->firstWhere('academic_year_id', $activeAY->id);
            if ($membership && $membership->extracurricular) {
                $membershipCode = $membership->extracurricular->code ?? '-';
            }
        }

        return [
            $student->id_number,
            $student->name,
            $student->studentClass?->name ?? '-',
            $student->enrollment_year,
            $student->grade->value,
            $membershipCode,
            $student->award ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Data Siswa Aktif';
    }
}