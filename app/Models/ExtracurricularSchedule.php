<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtracurricularSchedule extends Model
{
    use HasFactory;
    
    protected $table = 'extracurricular_schedules';

    protected $fillable = [
        'extracurricular_id',
        'day',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }
}
