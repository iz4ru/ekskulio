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

        $preview = [
            'type' => $isYearChange ? 'academic_year' : 'semester',
            'memberships_to_close' => ExtracurricularMembership::where('academic_year_id', $closingYear->id)->where('status', MembershipStatus::AKTIF->value)->count(),
            'students_to_graduate' => 0,
            'students_to_promote' => 0,
            'promoted_students' => 0,
            'graduated_students' => 0,
            'classes_created' => 0,
            'class_promotions' => [],
        ];

        if ($isYearChange) {
            $preview['students_to_graduate'] = Student::where('grade', StudentGrade::XII->value)->where('status', StudentStatus::AKTIF->value)->count();

            $preview['students_to_promote'] = $preview['promoted_students'] = Student::whereIn('grade', [StudentGrade::X->value, StudentGrade::XI->value])
                ->where('status', StudentStatus::AKTIF->value)
                ->count();

            $preview['class_promotions'] = $this->getClassPromotionPreview();
        }

        return $preview;
    }

    /**
     * Generate preview mapping kelas yang akan dipromosikan
     *
     * @return array<int, array{from: string, to: string, count: int}>
     */
    protected function getClassPromotionPreview(): array
    {
        $promotions = [];

        // Ambil semua kelas yang dipakai siswa X & XI yang aktif
        $classes = StudentClass::whereHas('students', function ($q) {
            $q->whereIn('grade', [StudentGrade::X->value, StudentGrade::XI->value])->where('status', StudentStatus::AKTIF->value);
        })
            ->withCount([
                'students as student_count' => function ($q) {
                    $q->whereIn('grade', [StudentGrade::X->value, StudentGrade::XI->value])->where('status', StudentStatus::AKTIF->value);
                },
            ])
            ->get();

        foreach ($classes as $class) {
            if ($class->student_count > 0) {
                $promotions[] = [
                    'from' => $class->name,
                    'to' => $this->promoteClassName($class->name),
                    'count' => $class->student_count,
                ];
            }
        }

        // Sort by class name for consistent display
        usort($promotions, fn($a, $b) => $a['from'] <=> $b['from']);

        return $promotions;
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
    public function close(AcademicYear $closingYear, AcademicYear $targetYear, array $classMappings = []): array
    {
        $isYearChange = $closingYear->year !== $targetYear->year;

        return DB::transaction(function () use ($closingYear, $targetYear, $isYearChange, $classMappings): array {
            $results = [
                'type' => $isYearChange ? 'academic_year' : 'semester',
                'closed_memberships' => 0,
                'graduated_students' => 0,
                'promoted_students' => 0,
                'classes_created' => 0,
            ];

            // 1. Tutup semua keanggotaan di periode yang ditutup
            $results['closed_memberships'] = ExtracurricularMembership::where('academic_year_id', $closingYear->id)
                ->where('status', MembershipStatus::AKTIF->value)
                ->update(['status' => MembershipStatus::SELESAI->value]);

            // 2. Jika ganti TAHUN AJARAN → Luluskan XII & Naikkan Kelas + Update Class
            if ($isYearChange) {
                // Snapshot ID semua grade SEBELUM mutasi apapun
                $gradeXIds  = Student::where('grade', StudentGrade::X->value)
                    ->where('status', StudentStatus::AKTIF->value)->pluck('id');

                $gradeXIIds = Student::where('grade', StudentGrade::XI->value)
                    ->where('status', StudentStatus::AKTIF->value)->pluck('id');

                // Build class map dari user input
                $classMap = collect($classMappings)
                    ->mapWithKeys(fn($mapping) => [
                        strtoupper(trim($mapping['from'])) => strtoupper(trim($mapping['to'])),
                    ])->toArray();

                // Luluskan XII (dengan last_class + null class_id)
                $graduatingStudents = Student::where('grade', StudentGrade::XII->value)
                    ->where('status', StudentStatus::AKTIF->value)
                    ->with('studentClass')
                    ->get();

                foreach ($graduatingStudents as $student) {
                    $student->update([
                        'status'     => StudentStatus::LULUS->value,
                        'class_id'   => null,
                        'last_class' => $student->studentClass?->name ?? null,
                    ]);
                }
                $results['graduated_students'] = $graduatingStudents->count();

                // Promote X → XI
                $results['promoted_students'] += $this->promoteStudentsWithClass(
                    $gradeXIds, StudentGrade::XI->value, $results, $classMap
                );

                // Promote XI → XII
                $results['promoted_students'] += $this->promoteStudentsWithClass(
                    $gradeXIIds, StudentGrade::XII->value, $results, $classMap
                );
            }

            // 3. Swap flag aktif
            $closingYear->update(['is_active' => false]);
            $targetYear->update(['is_active' => true]);

            Log::info('Academic year/semester closed', [
                'from' => $closingYear->year . ' ' . $closingYear->semester,
                'to' => $targetYear->year . ' ' . $targetYear->semester,
                'results' => $results,
            ]);

            return $results;
        });
    }

    /**
     * Promote students dari grade lama ke grade baru + update class_id
     */
    protected function promoteStudentsWithClass($studentIds, string $toGrade, array &$results, array $classMap = []): int
    {
        $results['classes_created'] ??= 0;
        $promotedCount = 0;

        Student::whereIn('id', $studentIds)
            ->where('status', StudentStatus::AKTIF->value)
            ->cursor()
            ->each(function ($student) use ($toGrade, &$results, &$promotedCount, $classMap): void {
                $currentClassName = strtoupper(trim($student->studentClass?->name ?? ''));

                // Pakai mapping dari user, fallback ke auto-generate
                $newClassName = $classMap[$currentClassName] ?? $this->promoteClassName($student->studentClass?->name);

                $newClass = StudentClass::firstOrCreate(['name' => $newClassName], ['is_active' => true]);

                if ($newClass->wasRecentlyCreated) {
                    $results['classes_created']++;
                }

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
        return preg_replace_callback(
            '/^(X|XI)(\s+.+)?$/',
            function ($matches) {
                $grade = $matches[1];
                $suffix = $matches[2] ?? ''; // Termasuk spasi awal jika ada

                $newGrade = $grade === 'X' ? 'XI' : 'XII';

                return $newGrade . $suffix;
            },
            $className,
        );
    }
}
