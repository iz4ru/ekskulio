<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    protected $table = 'extracurriculars';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(ExtracurricularUser::class);
    }

    public function schedules()
    {
        return $this->hasMany(ExtracurricularSchedule::class);
    }
}
