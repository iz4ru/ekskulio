<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtracurricularCategory extends Model
{
    use HasFactory;
    
    protected $table = 'extracurricular_categories';

    protected $fillable = [
        'id',
        'name',
        'code',
    ];

    public function extracurriculars()
    {
        return $this->hasMany(Extracurricular::class, 'category_id');
    }
}
