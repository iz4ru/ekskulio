<?php

namespace App\Models;

use App\Enums\PresenceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresenceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'presence_id',
        'membership_id',
        'student_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => PresenceStatus::class,
    ];

    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }

    public function membership()
    {
        return $this->belongsTo(ExtracurricularMembership::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
