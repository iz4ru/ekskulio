<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentTemplateExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Hanya siswa aktif (X, XI, XII yang belum lulus/mutasi)
        return Student::where('status', 'aktif')
            ->with('studentClass')
            ->orderBy('grade')->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return ['nis', 'nama_lengkap', 'kelas', 'tahun_masuk', 'extracurricular_id', 'penghargaan'];
    }

    public function map($student): array
    {
        return [
            $student->id_number,
            $student->name,
            $student->studentClass?->name ?? '-',
            $student->enrollment_year,
            '', // Kosongkan agar admin bisa pilih ekskul baru
            $student->award ?? '-',
        ];
    }
}
