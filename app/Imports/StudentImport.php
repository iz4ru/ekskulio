<?php

namespace App\Imports;

use App\Enums\MembershipStatus;
use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMembership;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;

class StudentImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation, WithEvents
{
    protected $defaultClassId;
    protected $importedBy;
    protected $importedCount = 0;

    public function __construct($defaultClassId = null, ?User $importedBy = null)
    {
        $this->defaultClassId = $defaultClassId;
        $this->importedBy = $importedBy;
    }

    /**
     * Normalisasi data sebelum validasi
     */
    public function prepareForValidation($data, $index)
    {
        return [
            'nis'                    => isset($data['nis']) ? (string) $data['nis'] : null,
            'nama_lengkap'           => isset($data['nama_lengkap']) ? (string) $data['nama_lengkap'] : null,
            'kelas'                  => isset($data['kelas']) ? (string) $data['kelas'] : null,
            'kelas_tujuan'           => isset($data['kelas_tujuan']) ? (string) $data['kelas_tujuan'] : null,
            'tahun_masuk'            => $data['tahun_masuk'] ?? null,
            'tingkat'                => isset($data['tingkat']) ? (string) $data['tingkat'] : null,
            'kode_ekstrakurikuler'   => isset($data['kode_ekstrakurikuler']) ? strtoupper(trim($data['kode_ekstrakurikuler'])) : null,
            'penghargaan'            => isset($data['penghargaan']) ? (string) $data['penghargaan'] : null,
        ];
    }

    public function model(array $row)
    {
        $classId = null;
 
        if (isset($row['kelas_tujuan']) && !empty($row['kelas_tujuan']) && $row['kelas_tujuan'] !== '-') {
            $targetClassName = ucwords(strtoupper(trim($row['kelas_tujuan'])));
            $targetClass     = StudentClass::firstOrCreate(
                ['name' => $targetClassName],
                ['name' => $targetClassName, 'is_active' => true]
            );
            $classId = $targetClass->id;
        } elseif (isset($row['kelas']) && !empty($row['kelas']) && $row['kelas'] !== '-') {
            $className    = ucwords(strtoupper(trim($row['kelas'])));
            $studentClass = StudentClass::firstOrCreate(
                ['name' => $className],
                ['name' => $className, 'is_active' => true]
            );
            $classId = $studentClass->id;
        } elseif ($this->defaultClassId) {
            $classId = $this->defaultClassId;
        }
 
        $grade = $this->calculateGrade((int) $row['tahun_masuk'], $row['tingkat'] ?? null);
 
        $studentData = [
            'name'            => ucwords(strtoupper($row['nama_lengkap'])),
            'class_id'        => $classId,
            'grade'           => $grade,
            'status'          => StudentStatus::AKTIF->value,
            'enrollment_year' => (int) $row['tahun_masuk'],
            'award'           => !empty($row['penghargaan']) && $row['penghargaan'] !== '-' ? $row['penghargaan'] : null,
        ];
 
        return DB::transaction(function () use ($row, $studentData) {
            $isNew   = false;
            $student = Student::where('id_number', $row['nis'])->first();
 
            if ($student) {
                $student->update($studentData);
            } else {
                $studentData['id_number'] = $row['nis'];
                $student = Student::create($studentData);
                $isNew   = true;
            }
 
            if (!empty($row['kode_ekstrakurikuler']) && $row['kode_ekstrakurikuler'] !== '-' && $row['kode_ekstrakurikuler'] !== '') {
                $activeAY = AcademicYear::getActiveYear();
 
                if ($activeAY) {
                    $ekskul = Extracurricular::where('code', $row['kode_ekstrakurikuler'])
                        ->where('is_active', true)
                        ->first();
 
                    if ($ekskul) {
                        ExtracurricularMembership::updateOrCreate(
                            [
                                'student_id'         => $student->id,
                                'extracurricular_id' => $ekskul->id,
                                'academic_year_id'   => $activeAY->id,
                            ],
                            ['status' => MembershipStatus::AKTIF->value]
                        );
                    } else {
                        Log::warning("Kode ekstrakurikuler '{$row['kode_ekstrakurikuler']}' tidak ditemukan atau tidak aktif untuk NIS {$row['nis']}.");
                    }
                }
            }
 
            $this->importedCount++;
 
            return $student;
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                if ($this->importedBy && $this->importedCount > 0) {
                    \App\Models\Log::create([
                        'user_id'  => $this->importedBy->id,
                        'activity' => 'Import siswa',
                        'detail'   => $this->importedBy->name . ' mengimpor ' . $this->importedCount . ' siswa',
                    ]);
                }
            },
        ];
    }

    /**
     * Calculate grade dari enrollment_year atau override manual
     */
    protected function calculateGrade(int $enrollmentYear, ?string $manualGrade = null): string
    {
        // Jika ada override tingkat di Excel, gunakan itu
        if (!empty($manualGrade)) {
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

        // Fallback: auto-calculate dari enrollment_year
        return Student::calculateGradeFromEnrollment($enrollmentYear);
    }

    public function rules(): array
    {
        return [
            'nis'                  => 'required|string|max:20',
            'nama_lengkap'         => 'required|string|max:255',
            'tahun_masuk'          => 'required|integer|digits:4|min:2000|max:2099',
            'kelas'                => 'nullable|string|max:50',
            'kelas_tujuan'         => 'nullable|string|max:50|different:kelas',
            'tingkat'              => 'nullable|string|in:X,XI,XII,10,11,12',
            'kode_ekstrakurikuler' => 'nullable|string|exists:extracurriculars,code',
            'penghargaan'          => 'nullable|string',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nis.required'                  => 'NIS wajib diisi pada baris :row',
            'nama_lengkap.required'         => 'Nama lengkap wajib diisi pada baris :row',
            'tahun_masuk.required'          => 'Tahun masuk wajib diisi pada baris :row',
            'tahun_masuk.integer'           => 'Tahun masuk harus berupa angka pada baris :row',
            'tahun_masuk.digits'            => 'Tahun masuk harus 4 digit pada baris :row',
            'tingkat.in'                    => 'Tingkat harus X, XI, XII, 10, 11, atau 12 pada baris :row',
            'kode_ekstrakurikuler.exists'   => 'Kode ekskul tidak terdaftar pada baris :row',
            'kelas.max'                     => 'Nama kelas maksimal 50 karakter pada baris :row',
            'kelas_tujuan.max'              => 'Nama kelas tujuan maksimal 50 karakter pada baris :row',
            'kelas_tujuan.different'        => 'Kelas tujuan tidak boleh sama dengan kelas asal pada baris :row',
        ];
    }
}