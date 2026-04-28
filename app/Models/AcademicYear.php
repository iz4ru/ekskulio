<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function memberships()
    {
        return $this->hasMany(ExtracurricularMembership::class, 'academic_year_id');
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->year} - ".ucfirst($this->semester);
    }

    public static function getActiveYear()
    {
        return self::where('is_active', true)->first();
    }

    public static function getNextYear(string $currentYear): string
    {
        $parts = explode('/', $currentYear);
        if (count($parts) !== 2) {
            return $currentYear;
        }

        return ($parts[0] + 1).'/'.($parts[1] + 1);
    }

    public function getNextAcademicYear(): ?self
    {
        $nextYearString = self::getNextYear($this->year);

        return self::where('year', $nextYearString)->first();
    }

    public function hasActiveMemberships(): bool
    {
        return $this->memberships()->where('status', 'aktif')->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
