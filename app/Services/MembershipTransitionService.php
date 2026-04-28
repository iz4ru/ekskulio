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
use Throwable;

class MembershipTransitionService
{
    /**
     * Transition active memberships from old academic year to new academic year.
     *
     * @return array{transferred: int, skipped: int, errors: array<string>}
     */
    public function transition(AcademicYear $oldYear, AcademicYear $newYear, bool $isAcademicYearChange = true): array
    {
        $results = [
            'transferred' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $activeMemberships = ExtracurricularMembership::query()
            ->with('student')
            ->byAcademicYear($oldYear->id)
            ->active()
            ->get();

        if ($activeMemberships->isEmpty()) {
            Log::info("No active memberships found for academic year: {$oldYear->year}");

            return $results;
        }

        DB::transaction(function () use ($activeMemberships, $oldYear, $newYear, &$results): void {
            $this->processMemberships($activeMemberships, $newYear, $results);
            $this->closeOldMemberships($activeMemberships, $oldYear, $results);
        });

        Log::info('Membership transition completed', [
            'from_year' => $oldYear->year,
            'to_year' => $newYear->year,
            'transferred' => $results['transferred'],
            'skipped' => $results['skipped'],
        ]);

        return $results;
    }

    /**
     * Get preview data for transition dry-run.
     *
     * @return array{total_active: int, to_promote: int, to_stop: int, details: array<string, mixed>}
     */
    public function getTransitionPreview(AcademicYear $oldYear, bool $isAcademicYearChange = true): array
    {
        $activeMemberships = ExtracurricularMembership::query()
            ->with('student')
            ->byAcademicYear($oldYear->id)
            ->active()
            ->get();

        $toMigrate = 0;
        $toClose = 0;

        foreach ($activeMemberships as $membership) {
            // Jika ganti semester: semua eligible. Jika ganti tahun: hanya X & XI.
            $eligible = ! $isAcademicYearChange
                ? $membership->student?->status === StudentStatus::AKTIF->value
                : $membership->student?->isEligibleForExtracurricular();

            if ($eligible) {
                $toMigrate++;
            } else {
                $toClose++;
            }
        }

        return [
            'total_active' => $activeMemberships->count(),
            'to_migrate' => $toMigrate,      // ✅ Key konsisten
            'to_close' => $toClose,          // ✅ Key konsisten
            'details' => [
                'grade_xii' => Student::where('grade', StudentGrade::XII->value)
                    ->where('status', StudentStatus::AKTIF->value)
                    ->count(),
            ],
        ];
    }

    /**
     * Get eligible students who are NOT yet enrolled in the given academic year.
     *
     * @return Collection<int, Student>
     */
    public function getEligibleStudentsForEnrollment(AcademicYear $academicYear): Collection
    {
        return Student::query()
            ->eligibleForExtracurricular()
            ->whereDoesntHave('memberships', function ($query) use ($academicYear): void {
                $query->byAcademicYear($academicYear->id);
            })
            ->orderBy('grade')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get students already enrolled in the given academic year.
     *
     * @return Collection<int, Student>
     */
    public function getStudentsAlreadyEnrolled(AcademicYear $academicYear): Collection
    {
        return Student::query()
            ->eligibleForExtracurricular()
            ->whereHas('memberships', function ($query) use ($academicYear): void {
                $query->byAcademicYear($academicYear->id);
            })
            ->with(['memberships' => function ($query) use ($academicYear): void {
                $query->byAcademicYear($academicYear->id);
            }])
            ->orderBy('grade')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a duplicate enrollment exists.
     */
    public function checkDuplicateEnrollment(int $studentId, int $extracurricularId, int $academicYearId): bool
    {
        return ExtracurricularMembership::query()
            ->where('student_id', $studentId)
            ->where('extracurricular_id', $extracurricularId)
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

    /**
     * Transition a single membership to a new academic year.
     */
    public function transitionSingle(
        ExtracurricularMembership $membership,
        AcademicYear $newAcademicYear,
        bool $isAcademicYearChange = true
    ): ?ExtracurricularMembership {
        if (! $membership->canBeTransferred()) {
            return null;
        }

        $student = $membership->student;

        if (! $this->isEligibleForTransition($student, $isAcademicYearChange)) {
            return null;
        }

        if ($this->checkDuplicateEnrollment(
            $membership->student_id,
            $membership->extracurricular_id,
            $newAcademicYear->id
        )) {
            return null;
        }

        try {
            return ExtracurricularMembership::create([
                'student_id' => $membership->student_id,
                'extracurricular_id' => $membership->extracurricular_id,
                'academic_year_id' => $newAcademicYear->id,
                'status' => MembershipStatus::AKTIF->value,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to transition membership', [
                'membership_id' => $membership->id,
                'new_year_id' => $newAcademicYear->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if student is eligible for membership transition.
     */
    protected function isEligibleForTransition(?Student $student, bool $isAcademicYearChange): bool
    {
        if (! $student || $student->status->value !== 'aktif') {
            return false;
        }

        // Ganti semester: semua aktif eligible
        if (! $isAcademicYearChange) {
            return true;
        }

        // Ganti tahun: hanya X & XI eligible
        return $student->grade->value !== StudentGrade::XII->value;
    }

    /**
     * Get the reason why a student is not eligible for transition.
     */
    protected function getStopReason(?Student $student): string
    {
        if ($student === null) {
            return 'status_not_aktif';
        }

        if ($student->grade === StudentGrade::XII->value) {
            return 'grade_xii';
        }

        return 'status_not_aktif';
    }

    /**
     * Process all memberships and create new ones for the new year.
     *
     * @param  Collection<int, ExtracurricularMembership>  $memberships
     * @param  array<string, int|array<string>>  $results
     */
    protected function processMemberships(
        Collection $memberships,
        AcademicYear $newYear,
        array &$results,
        bool $isAcademicYearChange = true
    ): void {
        foreach ($memberships as $membership) {
            $student = $membership->student;

            if (! $this->isEligibleForTransition($student, $isAcademicYearChange)) {
                $results['skipped']++;
                Log::info('Skipped membership transition', [
                    'membership_id' => $membership->id,
                    'student_id' => $student?->id,
                    'reason' => $this->getStopReason($student),
                ]);

                continue;
            }

            try {
                $this->transitionSingle($membership, $newYear, $isAcademicYearChange);
                $results['transferred']++;
            } catch (Throwable $e) {
                $results['errors'][] = "Failed to transfer membership {$membership->id}: {$e->getMessage()}";
                Log::error('Membership transition failed', [
                    'membership_id' => $membership->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Close all old memberships by setting status to 'selesai'.
     *
     * @param  Collection<int, ExtracurricularMembership>  $memberships
     * @param  array<string, int|array<string>>  $results
     */
    protected function closeOldMemberships(Collection $memberships, AcademicYear $oldYear, array &$results): void
    {
        $membershipIds = $memberships->pluck('id')->toArray();

        $closed = ExtracurricularMembership::query()
            ->whereIn('id', $membershipIds)
            ->where('academic_year_id', $oldYear->id)
            ->where('status', MembershipStatus::AKTIF->value)
            ->update(['status' => MembershipStatus::SELESAI->value]);

        Log::info('Closed old memberships', [
            'academic_year' => $oldYear->year,
            'closed_count' => $closed,
        ]);
    }
}
