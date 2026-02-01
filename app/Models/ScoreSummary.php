<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreSummary extends Model
{
    use HasFactory;
    
    protected $table = 'score_summaries';

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'average_score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
