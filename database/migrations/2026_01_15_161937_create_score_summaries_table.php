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
        Schema::create('score_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('extracurricular_memberships')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->float('score')->nullable()->default(0);
            $table->string('predicate', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['membership_id', 'academic_year_id'], 'unique_score_per_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_summaries');
    }
};
