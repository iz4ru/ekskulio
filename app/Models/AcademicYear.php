<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicYear extends Model
{
    use HasFactory;
    
    protected $table = 'academic_years';
    protected $fillable = [
        'year', 
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute()
    {
        return "{$this->year} - " . ucfirst($this->semester);
    }

    public static function getActiveYear()
    {
        return self::where('is_active', true)->first();
    }
}
