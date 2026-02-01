<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Extracurricular extends Model
{
    use HasFactory;
    
    protected $table = 'extracurriculars';

    protected $fillable = [
        'id',
        'name',
        'code',
        'award',
        'category_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExtracurricularCategory::class, 'category_id');
    }

    public function users()
    {
        return $this->hasMany(ExtracurricularUser::class, 'extracurricular_id');
    }

    public function schedules()
    {
        return $this->hasMany(ExtracurricularSchedule::class, 'extracurricular_id');
    }

    public function presenceSummaries()
    {
        return $this->hasMany(PresenceSummary::class, 'extracurricular_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'extracurricular_id');
    }
}
