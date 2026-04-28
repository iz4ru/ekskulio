<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Enums\StudentGrade;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularMembership extends Model
{
    use HasFactory;

    protected $table = 'extracurricular_memberships';

    protected $fillable = [
        'student_id',
        'extracurricular_id',
        'academic_year_id',
        'status',
    ];

    protected $casts = [
        'status' => MembershipStatus::class,
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function presenceDetails()
    {
        return $this->hasMany(PresenceDetail::class, 'membership_id');
    }

    public function scores()
    {
        return $this->hasMany(ScoreSummary::class, 'membership_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', MembershipStatus::AKTIF->value);
    }

    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeByExtracurricular($query, $extracurricularId)
    {
        return $query->where('extracurricular_id', $extracurricularId);
    }

    public function scopeEligibleStudents($query)
    {
        return $query->whereHas('student', function ($q) {
            $q->where('status', StudentStatus::AKTIF->value)
                ->where('grade', '!=', StudentGrade::XII->value);
        });
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::AKTIF;
    }

    public function canBeTransferred(): bool
    {
        return $this->status === MembershipStatus::AKTIF;
    }

    public function markAsCompleted(): bool
    {
        return $this->update(['status' => MembershipStatus::SELESAI->value]);
    }

    public function markAsDropped(): bool
    {
        return $this->update(['status' => MembershipStatus::DROP->value]);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof \BackedEnum ? $this->status->value : $this->status;
    }
}
