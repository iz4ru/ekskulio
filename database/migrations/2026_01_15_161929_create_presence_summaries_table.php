<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presence_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->integer('present_count')->default(0);
            $table->integer('sick_count')->default(0);
            $table->integer('permission_count')->default(0);
            $table->integer('absent_count')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'extracurricular_id'], 'unique_presence_summary');

            $table->index(['academic_year_id', 'extracurricular_id'], 'idx_summary_year_ekskul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presence_summaries');
    }
};
