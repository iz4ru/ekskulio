<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularUser extends Model
{
    protected $table = 'extracurricular_users';

    protected $fillable = [
        'extracurricular_id',
        'user_id',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
