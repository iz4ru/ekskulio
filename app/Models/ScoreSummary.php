<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreSummary extends Model
{
    use HasFactory;

    protected $table = 'score_summaries';

    protected $fillable = [
        'membership_id',
        'academic_year_id',
        'score',
        'predicate',
        'notes',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function membership()
    {
        return $this->belongsTo(ExtracurricularMembership::class, 'membership_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'membership_id')->via('membership');
    }

    public static function getPredicateFromScore(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }

    public static function getPredicateLabel(string $predicate): string
    {
        return match ($predicate) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
            'E' => 'Sangat Kurang',
            default => '-',
        };
    }
}
