<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularUser extends Model
{
    use HasFactory;
    
    protected $table = 'extracurricular_users';

    protected $fillable = [
        'extracurricular_id',
        'user_id',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
