<?php

namespace App\Models;

use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use BackedEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'id_number',
        'uuid',
        'name',
        'class_id',
        'grade',
        'status',
        'enrollment_year',
        'award',
    ];

    protected $casts = [
        'grade' => StudentGrade::class,
        'status' => StudentStatus::class,
    ];

    public const GRADE_HIERARCHY = [
        StudentGrade::X->value => StudentGrade::XI->value,
        StudentGrade::XI->value => StudentGrade::XII->value,
        StudentGrade::XII->value => null,
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function memberships()
    {
        return $this->hasMany(ExtracurricularMembership::class);
    }

    public function activeMemberships()
    {
        return $this->memberships()->where('status', 'aktif');
    }

    public function scopeEligibleForExtracurricular($query)
    {
        return $query->where('status', StudentStatus::AKTIF->value)
            ->where('grade', '!=', StudentGrade::XII->value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', StudentStatus::AKTIF->value)->withoutTrashed();
    }

    public function scopeArchived($query)
    {
        return $query->whereIn('status', [StudentStatus::LULUS->value, StudentStatus::MUTASI->value])
            ->withoutTrashed();
    }

    public function scopeNotGraduated($query)
    {
        return $query->where('status', '!=', StudentStatus::LULUS->value)->withoutTrashed();
    }

    public function scopeByGrade($query, $grade)
    {
        $gradeValue = $grade instanceof BackedEnum ? $grade->value : $grade;

        return $query->where('grade', $gradeValue);
    }

    public function isEligibleForExtracurricular(): bool
    {
        $statusValue = $this->status instanceof BackedEnum ? $this->status->value : $this->status;
        $gradeValue = $this->grade instanceof BackedEnum ? $this->grade->value : $this->grade;

        return $statusValue === StudentStatus::AKTIF->value && $gradeValue !== StudentGrade::XII->value;
    }

    public function promoteToNextGrade(): bool
    {
        $currentGrade = $this->grade instanceof BackedEnum ? $this->grade->value : $this->grade;
        $nextGrade = self::GRADE_HIERARCHY[$currentGrade] ?? null;

        if ($nextGrade === null) {
            $this->update(['status' => StudentStatus::LULUS->value]);

            return false;
        }

        $this->update(['grade' => $nextGrade]);

        return true;
    }

    public static function calculateGradeFromEnrollment(int $enrollmentYear, ?int $academicYearStart = null): string
    {
        $currentYear = $academicYearStart ?? (int) date('Y');
        $yearsDiff = $currentYear - $enrollmentYear;

        return match (true) {
            $yearsDiff <= 0 => StudentGrade::X->value,
            $yearsDiff === 1 => StudentGrade::XI->value,
            $yearsDiff >= 2 => StudentGrade::XII->value,
            default => StudentGrade::X->value,
        };
    }

    public function getCalculatedGradeAttribute(): ?string
    {
        $activeAY = AcademicYear::getActiveYear();
        if (! $activeAY) {
            return null;
        }

        $startYear = (int) substr($activeAY->year, 0, 4);

        return self::calculateGradeFromEnrollment($this->enrollment_year, $startYear);
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getGradeValueAttribute(): string
    {
        return $this->grade instanceof BackedEnum ? $this->grade->value : $this->grade;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof BackedEnum ? $this->status->value : $this->status;
    }
}
