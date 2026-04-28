<?php

namespace App\Imports;

use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\StudentClass;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
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
            'tingkat' => isset($data['tingkat']) ? (string) $data['tingkat'] : null,
            'penghargaan' => isset($data['penghargaan']) ? (string) $data['penghargaan'] : null,
            'tahun_masuk' => $data['tahun_masuk'] ?? null,
        ];
    }

    public function model(array $row)
    {
        $classId = null;

        if (isset($row['kelas']) && ! empty($row['kelas'])) {
            $className = ucwords(strtoupper(trim($row['kelas'])));
            $studentClass = StudentClass::firstOrCreate(['name' => $className], ['name' => $className, 'is_active' => true]);
            $classId = $studentClass->id;
        } elseif ($this->defaultClassId) {
            $classId = $this->defaultClassId;
        }

        $grade = $this->calculateGrade(
            (int) $row['tahun_masuk'],
            $row['tingkat'] ?? null
        );

        $data = [
            'id_number' => $row['nis'],
            'name' => ucwords(strtoupper($row['nama_lengkap'])),
            'class_id' => $classId,
            'grade' => $grade,
            'status' => StudentStatus::AKTIF->value,
            'enrollment_year' => $row['tahun_masuk'],
            'award' => ! empty($row['penghargaan']) && $row['penghargaan'] !== '-' ? $row['penghargaan'] : null,
        ];

        return new Student($data);
    }

    protected function calculateGrade(int $enrollmentYear, ?string $manualGrade = null): string
    {
        if (! empty($manualGrade)) {
            $normalized = strtoupper(trim($manualGrade));

            $gradeMap = [
                'X' => StudentGrade::X->value,
                '10' => StudentGrade::X->value,
                'SEPULUH' => StudentGrade::X->value,
                'XI' => StudentGrade::XI->value,
                '11' => StudentGrade::XI->value,
                'SEBELAS' => StudentGrade::XI->value,
                'XII' => StudentGrade::XII->value,
                '12' => StudentGrade::XII->value,
                'DUA BELAS' => StudentGrade::XII->value,
            ];

            if (isset($gradeMap[$normalized])) {
                return $gradeMap[$normalized];
            }
        }

        return Student::calculateGradeFromEnrollment($enrollmentYear);
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20|unique:students,id_number',
            'nama_lengkap' => 'required|string|max:255',
            'tahun_masuk' => 'required|integer|digits:4|min:2000|max:2099',
            'kelas' => 'nullable|string|max:50',
            'tingkat' => 'nullable|string|max:10',
            'penghargaan' => 'nullable|string',
        ];
    }

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
            'penghargaan.string' => 'Penghargaan harus berupa teks pada baris :row',
            'kelas.max' => 'Nama kelas maksimal 50 karakter pada baris :row',
        ];
    }
}
