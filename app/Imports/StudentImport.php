<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentClass;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $defaultClassId;

    public function __construct($defaultClassId = null)
    {
        $this->defaultClassId = $defaultClassId;
    }

    public function prepareForValidation($data, $index)
    {
        return [
            'nis' => isset($data['nis']) ? (string) $data['nis'] : null,
            'nama_lengkap' => isset($data['nama_lengkap']) ? (string) $data['nama_lengkap'] : null,
            'kelas' => isset($data['kelas']) ? (string) $data['kelas'] : null,
            'penghargaan' => isset($data['penghargaan']) ? (string) $data['penghargaan'] : null,
            'tahun_masuk' => $data['tahun_masuk'] ?? null,
            'extracurricular_id' => $data['extracurricular_id'] ?? null,
        ];
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // ✅ 1. Tentukan class_id (prioritas: Excel > Form dropdown)
        $classId = null;

        // Jika ada kolom 'kelas' di Excel dan tidak kosong
        if (isset($row['kelas']) && !empty($row['kelas'])) {
            // Cari atau buat kelas baru
            $className = ucwords(strtoupper(trim($row['kelas'])));

            $studentClass = StudentClass::firstOrCreate(['name' => $className], ['name' => $className, 'is_active' => true]);

            $classId = $studentClass->id;
        }
        // Jika tidak ada di Excel, pakai dari form dropdown
        elseif ($this->defaultClassId) {
            $classId = $this->defaultClassId;
        }

        // ✅ 2. Tentukan extracurricular_id
        $extracurricularId = null;
        if (isset($row['extracurricular_id']) && !empty($row['extracurricular_id'])) {
            $extracurricularId = $row['extracurricular_id'];
        }

        // ✅ 3. Build data siswa
        $data = [
            'id_number' => $row['nis'],
            'name' => ucwords(strtoupper($row['nama_lengkap'])),
            'class_id' => $classId,
            'enrollment_year' => $row['tahun_masuk'],
            'award' => !empty($row['penghargaan']) && $row['penghargaan'] !== '-' ? $row['penghargaan'] : null,
            'extracurricular_id' => $extracurricularId,
        ];

        return new Student($data);
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20|unique:students,id_number',
            'nama_lengkap' => 'required|string|max:255',
            'tahun_masuk' => 'required|integer|digits:4|min:2000|max:2099',
            'kelas' => 'nullable|string|max:50',
            'extracurricular_id' => 'nullable|integer|exists:extracurriculars,id',
            'penghargaan' => 'nullable|string',
        ];
    }

    /**
     * Custom Validation Messages
     */
    public function customValidationMessages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi pada baris :row',
            'nis.unique' => 'NIS :input sudah terdaftar pada baris :row',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi pada baris :row',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter pada baris :row',
            'tahun_masuk.required' => 'Tahun masuk wajib diisi pada baris :row',
            'tahun_masuk.integer' => 'Tahun masuk harus berupa angka pada baris :row',
            'tahun_masuk.digits' => 'Tahun masuk harus 4 digit pada baris :row',
            'tahun_masuk.min' => 'Tahun masuk tidak valid pada baris :row',
            'tahun_masuk.max' => 'Tahun masuk tidak valid pada baris :row',
            'extracurricular_id.exists' => 'ID Ekstrakurikuler :input tidak valid pada baris :row',
            'penghargaan.string' => 'Penghargaan harus berupa teks pada baris :row',
            'kelas.max' => 'Nama kelas maksimal 50 karakter pada baris :row',
        ];
    }
}
