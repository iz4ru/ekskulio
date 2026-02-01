<?php

namespace App\Models;

use App\Models\AcademicYear;
use App\Models\PresenceDetail;
use App\Models\Extracurricular;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presence extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'academic_year_id',
        'date',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getDayAttribute()
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return $days[$this->date->format('l')] ?? '-';
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function details()
    {
        return $this->hasMany(PresenceDetail::class, 'presence_id');
    }

    // ✅ Helper: Hitung statistik
    public function getPresentCountAttribute()
    {
        return $this->details()->where('status', 'present')->count();
    }

    public function getSickCountAttribute()
    {
        return $this->details()->where('status', 'sick')->count();
    }

    public function getPermissionCountAttribute()
    {
        return $this->details()->where('status', 'permission')->count();
    }

    public function getAbsentCountAttribute()
    {
        return $this->details()->where('status', 'absent')->count();
    }
}
