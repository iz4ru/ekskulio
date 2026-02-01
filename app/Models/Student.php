<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'id_number',
        'uuid',
        'name',
        'class_id',
        'enrollment_year',
        'award',
        'extracurricular_id',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'extracurricular_id');
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
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
}
