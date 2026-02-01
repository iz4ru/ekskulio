<?php

namespace Database\Seeders;

use App\Models\ExtracurricularCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExtracurricularCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExtracurricularCategory::create(['name' => 'Olahraga dan Bela Diri']);
        ExtracurricularCategory::create(['name' => 'Seni dan Budaya']);
        ExtracurricularCategory::create(['name' => 'Bahasa dan Literasi']);
        ExtracurricularCategory::create(['name' => 'Organisasi dan Kepemimpinan']);
        ExtracurricularCategory::create(['name' => 'Keterampilan Vokasional']);
        ExtracurricularCategory::create(['name' => 'Keagamaan']);
        ExtracurricularCategory::create(['name' => 'Teknologi']);
    }
}
