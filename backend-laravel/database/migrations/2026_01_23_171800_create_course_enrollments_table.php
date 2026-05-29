<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['enrolled', 'completed', 'dropped'])->default('enrolled');
            $table->timestamp('enrollment_date')->useCurrent();
            $table->timestamp('completion_date')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
            $table->index('course_id');
            $table->index('student_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
