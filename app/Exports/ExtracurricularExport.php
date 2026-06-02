<?php

namespace App\Exports;

use App\Models\Extracurricular;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExtracurricularExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Extracurricular::with('category')
        ->orderBy('name')
        ->get();
    }

    public function headings(): array
    {
        return ['id', 'nama_ekstrakurikuler', 'kode_ekstrakurikuler', 'kode_kategori_ekstrakurikuler', 'deskripsi', 'penghargaan', 'status'];
    }

    public function map($extracurricular): array
    {
        return [
            $extracurricular->id,
            $extracurricular->name,
            $extracurricular->code,
            $extracurricular->category?->code ?? '-',
            $extracurricular->description ?? '-',
            $extracurricular->award ?? '-',
            $extracurricular->is_active ? 'Aktif' : 'Tidak Aktif',
        ];
    }
}
