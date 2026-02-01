<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Presence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PresenceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'presence_id',
        'student_id',
        'status',
        'notes',
    ];

    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
