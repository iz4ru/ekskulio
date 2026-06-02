<?php

namespace App\Services;

use App\Enums\MembershipStatus;
use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\ExtracurricularMembership;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicYearClosureService
{
    /**
     * Preview data sebelum eksekusi
     */
    public function getPreview(AcademicYear $closingYear, AcademicYear $targetYear): array
    {
        $isYearChange = $closingYear->year !== $targetYear->year;

        return [
            'type' => $isYearChange ? 'academic_year' : 'semester',
            'memberships_to_close' => ExtracurricularMembership::where('academic_year_id', $closingYear->id)
                ->where('status', MembershipStatus::AKTIF->value)->count(),
            'students_to_graduate' => $isYearChange ? Student::where('grade', StudentGrade::XII->value)
                ->where('status', StudentStatus::AKTIF->value)->count() : 0,
            'students_to_promote'  => $isYearChange ? Student::whereIn('grade', [StudentGrade::X->value, StudentGrade::XI->value])
                ->where('status', StudentStatus::AKTIF->value)->count() : 0,
            'classes_to_create'    => $isYearChange ? $this->countNewClassesNeeded() : 0,
        ];
    }

    /**
     * Hitung estimasi kelas baru yang perlu dibuat (untuk preview)
     */
    protected function countNewClassesNeeded(): int
    {
        $count = 0;
        
        // Cek kelas X yang akan jadi XI
        Student::where('grade', StudentGrade::X->value)
            ->where('status', StudentStatus::AKTIF->value)
            ->whereNotNull('class_id')
            ->with('studentClass')
            ->cursor()
            ->each(function ($student) use (&$count) {
                $newClassName = $this->promoteClassName($student->studentClass?->name);
                if (!StudentClass::where('name', $newClassName)->exists()) {
                    $count++;
                }
            });

        // Cek kelas XI yang akan jadi XII
        Student::where('grade', StudentGrade::XI->value)
            ->where('status', StudentStatus::AKTIF->value)
            ->whereNotNull('class_id')
            ->with('studentClass')
            ->cursor()
            ->each(function ($student) use (&$count) {
                $newClassName = $this->promoteClassName($student->studentClass?->name);
                if (!StudentClass::where('name', $newClassName)->exists()) {
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Eksekusi penutupan tahun ajaran / semester
     */
    public function close(AcademicYear $closingYear, AcademicYear $targetYear): array
    {
        $isYearChange = $closingYear->year !== $targetYear->year;

        return DB::transaction(function () use ($closingYear, $targetYear, $isYearChange): array {
            $results = [
                'type' => $isYearChange ? 'academic_year' : 'semester',
                'closed_memberships' => 0,
                'graduated_students' => 0,
                'promoted_students'  => 0,
                'classes_created'    => 0,
            ];

            // 1. Tutup semua keanggotaan di periode yang ditutup
            $results['closed_memberships'] = ExtracurricularMembership::where('academic_year_id', $closingYear->id)
                ->where('status', MembershipStatus::AKTIF->value)
                ->update(['status' => MembershipStatus::SELESAI->value]);

            // 2. Jika ganti TAHUN AJARAN → Luluskan XII & Naikkan Kelas + Update Class
            if ($isYearChange) {
                // 2a. Luluskan siswa kelas XII
                $results['graduated_students'] = Student::where('grade', StudentGrade::XII->value)
                    ->where('status', StudentStatus::AKTIF->value)
                    ->update(['status' => StudentStatus::LULUS->value]);

                // 2b. Naikkan kelas X → XI + update class_id
                $results['promoted_students'] += $this->promoteStudentsWithClass(
                    StudentGrade::X->value,
                    StudentGrade::XI->value,
                    $results
                );

                // 2c. Naikkan kelas XI → XII + update class_id
                $results['promoted_students'] += $this->promoteStudentsWithClass(
                    StudentGrade::XI->value,
                    StudentGrade::XII->value,
                    $results
                );
            }

            // 3. Swap flag aktif
            $closingYear->update(['is_active' => false]);
            $targetYear->update(['is_active' => true]);

            Log::info('Academic year/semester closed', [
                'from' => $closingYear->year . ' ' . $closingYear->semester,
                'to'   => $targetYear->year . ' ' . $targetYear->semester,
                'results' => $results,
            ]);

            return $results;
        });
    }

    /**
     * Promote students dari grade lama ke grade baru + update class_id
     */
    protected function promoteStudentsWithClass(string $fromGrade, string $toGrade, array &$results): int
    {
        $promotedCount = 0;

        // Gunakan cursor untuk hemat memori pada dataset besar
        Student::where('grade', $fromGrade)
            ->where('status', StudentStatus::AKTIF->value)
            ->cursor()
            ->each(function ($student) use ($toGrade, &$results, &$promotedCount): void {
                $newClassName = $this->promoteClassName($student->studentClass?->name);
                
                // Cari atau buat kelas tujuan
                $newClass = StudentClass::firstOrCreate(
                    ['name' => $newClassName],
                    ['name' => $newClassName, 'is_active' => true]
                );

                // Track jika kelas baru dibuat
                if ($newClass->wasRecentlyCreated) {
                    $results['classes_created']++;
                }

                // Update grade dan class_id
                $student->update([
                    'grade' => $toGrade,
                    'class_id' => $newClass->id,
                ]);

                $promotedCount++;
            });

        return $promotedCount;
    }

    /**
     * Generate nama kelas baru berdasarkan pola promosi
     * 
     * Pattern: 
     * - "X RPL 1" → "XI RPL 1"
     * - "XI TKJ 2" → "XII TKJ 2"
     * - "X" → "XI"
     */
    protected function promoteClassName(?string $className): string
    {
        if (!$className || trim($className) === '') {
            return 'UMUM'; // Default fallback
        }

        $className = strtoupper(trim($className));

        // Pattern matching: Grade di awal, diikuti opsional suffix (spasi + jurusan + nomor)
        return preg_replace_callback('/^(X|XI)(\s+.+)?$/', function ($matches) {
            $grade = $matches[1];
            $suffix = $matches[2] ?? ''; // Termasuk spasi awal jika ada

            $newGrade = $grade === 'X' ? 'XI' : 'XII';

            return $newGrade . $suffix;
        }, $className);
    }
}