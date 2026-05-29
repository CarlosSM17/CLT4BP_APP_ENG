<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->json('responses'); // Array de respuestas
            $table->decimal('score', 5, 2)->nullable(); // Puntaje calculado
            $table->integer('time_spent')->nullable(); // Tiempo en segundos
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
            $table->index('assessment_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_responses');
    }
};
