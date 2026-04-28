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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('id_number')->unique();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->foreignId('class_id')->nullable()->constrained('student_classes')->nullOnDelete();
            $table->enum('grade', ['X', 'XI', 'XII'])->nullable();
            $table->enum('status', ['aktif', 'lulus', 'mutasi'])->default('aktif');
            $table->year('enrollment_year');
            $table->string('award')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
