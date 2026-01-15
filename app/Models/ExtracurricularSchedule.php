<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularSchedule extends Model
{
    protected $table = 'extracurricular_schedules';

    protected $fillable = [
        'extracurricular_id',
        'day',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
