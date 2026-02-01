<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresenceSummary extends Model
{
    use HasFactory;
    
    protected $table = 'presence_summaries';

    protected $fillable = [
        'student_id',
        'extracurricular_id',
        'academic_year_id',
        'present_count',
        'absent_count',
        'permission_count',
        'sick_count',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
