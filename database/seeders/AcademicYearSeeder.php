<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::create([
            'year' => '2025/2026',
            'semester' => 'ganjil',
            'is_active' => false,
        ]);

        AcademicYear::create([
            'year' => '2025/2026',
            'semester' => 'genap',
            'is_active' => true,
        ]);

        AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => false,
        ]);
    }
}
