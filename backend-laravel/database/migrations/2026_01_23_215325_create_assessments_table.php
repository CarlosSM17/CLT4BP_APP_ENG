<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('assessment_type', [
                'recall_initial',
                'comprehension_initial',
                'mslq_motivation_initial',
                'mslq_strategies',
                'recall_final',
                'comprehension_final',
                'cognitive_load',
                'mslq_motivation_final',
                'course_interest',
                'imms'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('questions');
            $table->json('config')->nullable(); // Configuración adicional (tiempo, peso, etc)
            $table->boolean('is_active')->default(false);
            $table->integer('time_limit')->nullable(); // en minutos
            $table->timestamps();

            $table->index('course_id');
            $table->index('assessment_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
