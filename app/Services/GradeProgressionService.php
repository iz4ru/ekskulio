<?php

namespace App\Services;

use App\Enums\MembershipStatus;
use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\ExtracurricularMembership;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeProgressionService
{
    public function __construct(
        private MembershipTransitionService $membershipService
    ) {}

    /**
     * Process year transition: promote students and migrate memberships.
     *
     * @return array{promoted: int, graduated: int, memberships_transitioned: int, memberships_completed: int, errors: array<string>}
     */
    public function processYearTransition(AcademicYear $fromYear, AcademicYear $toYear): array
    {
        $isAcademicYearChange = $fromYear->year !== $toYear->year;

        return DB::transaction(function () use ($fromYear, $toYear, $isAcademicYearChange): array {
            $results = [
                'type' => $isAcademicYearChange ? 'academic_year' : 'semester',
                'promoted' => 0,
                'graduated' => 0,
                'memberships_migrated' => 0,
                'errors' => [],
            ];

            // 1. MIGRATE MEMBERSHIP DULUAN (saat grade masih X/XI)
            if ($isAcademicYearChange) {
                // 1. Transisi membership dulu
                $transitionResult = $this->membershipService->transition($fromYear, $toYear, $isAcademicYearChange);
                $results['memberships_migrated'] = $transitionResult['transferred'];
                $results['errors'] = array_merge($results['errors'], $transitionResult['errors']);

                // 2. Grade progression & hitung graduated TERPISAH
                $promotedX = Student::where('grade', StudentGrade::X->value)
                    ->where('status', StudentStatus::AKTIF->value)
                    ->update(['grade' => StudentGrade::XI->value]);

                $promotedXI = Student::where('grade', StudentGrade::XI->value)
                    ->where('status', StudentStatus::AKTIF->value)
                    ->update(['grade' => StudentGrade::XII->value]);

                // ✅ Hitung graduated dari update siswa kelas XII
                $graduated = Student::where('grade', StudentGrade::XII->value)
                    ->where('status', StudentStatus::AKTIF->value)
                    ->update(['status' => StudentStatus::LULUS->value]);

                $results['promoted'] = ($promotedX ?: 0) + ($promotedXI ?: 0);
                $results['graduated'] = $graduated ?: 0; // ✅ Sumber yang benar
            } else {
                // Ganti semester: pindahkan semua membership tanpa mengubah eligible
                $activeMemberships = ExtracurricularMembership::query()
                    ->where('academic_year_id', $fromYear->id)
                    ->where('status', MembershipStatus::AKTIF->value)
                    ->cursor();

                foreach ($activeMemberships as $membership) {
                    // Cek duplikat
                    $exists = ExtracurricularMembership::where('student_id', $membership->student_id)
                        ->where('extracurricular_id', $membership->extracurricular_id)
                        ->where('academic_year_id', $toYear->id)
                        ->exists();

                    if (! $exists) {
                        ExtracurricularMembership::create([
                            'student_id' => $membership->student_id,
                            'extracurricular_id' => $membership->extracurricular_id,
                            'academic_year_id' => $toYear->id,
                            'status' => MembershipStatus::AKTIF->value,
                        ]);
                        $results['memberships_migrated']++;
                    }

                    $membership->update(['status' => MembershipStatus::SELESAI->value]);
                }
            }

            // 2. GRADE PROGRESSION (HANYA JIKA GANTI TAHUN AJARAN)
            if ($isAcademicYearChange) {
                $promotedX = Student::where('grade', StudentGrade::X->value)->where('status', StudentStatus::AKTIF->value)->update(['grade' => StudentGrade::XI->value]);
                $promotedXI = Student::where('grade', StudentGrade::XI->value)->where('status', StudentStatus::AKTIF->value)->update(['grade' => StudentGrade::XII->value]);

                $results['promoted'] = ($promotedX ?: 0) + ($promotedXI ?: 0);
            }

            // 3. SWAP FLAG ACTIVE
            $fromYear->update(['is_active' => false]);
            $toYear->update(['is_active' => true]);

            return $results;
        });
    }

    /**
     * Get preview data for year transition dry-run.
     *
     * @return array{students: array{grade_x: int, grade_xi: int, grade_xii: int}, memberships: array{total_active: int, to_promote: int, to_stop: int}, warning: string, type: string}
     */
    public function getTransitionPreview(AcademicYear $fromYear, AcademicYear $toYear): array
    {
        $isAcademicYearChange = $fromYear->year !== $toYear->year;

        if ($isAcademicYearChange) {
            $gradeX = Student::where('grade', StudentGrade::X->value)->where('status', StudentStatus::AKTIF->value)->count();
            $gradeXI = Student::where('grade', StudentGrade::XI->value)->where('status', StudentStatus::AKTIF->value)->count();
            $gradeXII = Student::where('grade', StudentGrade::XII->value)->where('status', StudentStatus::AKTIF->value)->count();

            $preview = $this->membershipService->getTransitionPreview($fromYear, $isAcademicYearChange);

            $toMigrate = $preview['to_migrate'];  // bukan to_promote
            $toClose = $preview['to_close'];      // bukan to_stop

            return [
                'type' => 'academic_year',
                'title' => 'Transisi Tahun Ajaran',
                'description' => 'Siswa kelas XII akan otomatis lulus, keanggotaan dipindahkan, dan grade dinaikkan.',
                'students' => [
                    'x' => $gradeX,
                    'xi' => $gradeXI,
                    'xii' => $gradeXII,
                    'will_promote' => $gradeX + $gradeXI,
                    'will_graduate' => $gradeXII,
                ],
                'memberships' => [
                    'total_active' => $preview['total_active'],
                    'to_migrate' => $toMigrate,
                    'to_close' => $toClose,
                ],
                'warning' => "{$gradeXII} siswa kelas XII akan lulus dan {$toClose} keanggotaan akan ditutup.",
            ];
        }

        return [
            'type' => 'semester',
            'title' => 'Ganti Semester',
            'description' => 'Grade siswa tidak berubah. Hanya keanggotaan yang dipindah ke semester baru.',
            'students' => ['affected' => 0],
            'memberships' => ['to_migrate' => ExtracurricularMembership::where('academic_year_id', $fromYear->id)->where('status', MembershipStatus::AKTIF->value)->count()],
            'warning' => 'Grade siswa tetap. Semua keanggotaan aktif akan dipindahkan.',
        ];
    }

    /**
     * Process student grade progression.
     *
     * @param  array<string, int|array<string>>  $results
     */
    protected function processStudentProgression(array &$results): void
    {
        $students = Student::query()
            ->where('status', StudentStatus::AKTIF->value)
            ->whereIn('grade', [StudentGrade::X->value, StudentGrade::XI->value])
            ->get();

        foreach ($students as $student) {
            $wasPromoted = $student->promoteToNextGrade();

            if ($wasPromoted) {
                $results['promoted']++;
                Log::info('Student promoted', [
                    'student_id' => $student->id,
                    'new_grade' => $student->grade,
                ]);
            } else {
                $results['graduated']++;
                Log::info('Student graduated', [
                    'student_id' => $student->id,
                    'status' => $student->status,
                ]);
            }
        }
    }

    /**
     * Process membership transition from old year to new year.
     *
     * @param  array<string, int|array<string>>  $results
     */
    protected function processMembershipTransition(AcademicYear $fromYear, AcademicYear $toYear, array &$results): void
    {
        $transitionResult = $this->membershipService->transition($fromYear, $toYear);

        $results['memberships_transitioned'] = $transitionResult['transferred'];
        $results['memberships_completed'] = $transitionResult['skipped'];
        $results['errors'] = array_merge($results['errors'], $transitionResult['errors']);
    }

    /**
     * Get student count by grade ready for promotion.
     *
     * @return array<string, int>
     */
    public function getStudentsReadyForPromotion(): array
    {
        return [
            'grade_x' => Student::query()
                ->where('grade', StudentGrade::X->value)
                ->where('status', StudentStatus::AKTIF->value)
                ->count(),
            'grade_xi' => Student::query()
                ->where('grade', StudentGrade::XI->value)
                ->where('status', StudentStatus::AKTIF->value)
                ->count(),
            'grade_xii' => Student::query()
                ->where('grade', StudentGrade::XII->value)
                ->where('status', StudentStatus::AKTIF->value)
                ->count(),
        ];
    }

    /**
     * Get student distribution by grade.
     *
     * @return Collection<int, array{grade: string, count: int}>
     */
    public function getStudentsByGrade(): Collection
    {
        return Student::query()
            ->notGraduated()
            ->active()
            ->selectRaw('grade, COUNT(*) as count')
            ->groupBy('grade')
            ->get()
            ->map(fn ($item) => [
                'grade' => $item->grade->value,
                'count' => (int) $item->count,
            ]);
    }
}
