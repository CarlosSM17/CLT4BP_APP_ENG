<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar campos de plantilla y calificación manual a assessments
        Schema::table('assessments', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('time_limit');
            $table->boolean('requires_manual_grading')->default(false)->after('is_template');
            $table->foreignId('source_template_id')
                ->nullable()
                ->after('requires_manual_grading')
                ->constrained('assessments')
                ->nullOnDelete();
        });

        // Modificar course_id para que sea nullable (para plantillas)
        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->change();
        });

        // Agregar campos de calificación manual a student_responses
        Schema::table('student_responses', function (Blueprint $table) {
            $table->string('grading_status')->default('auto_graded')->after('completed_at');
            $table->json('manual_scores')->nullable()->after('grading_status');
            $table->foreignId('graded_by')
                ->nullable()
                ->after('manual_scores')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });

        // Agregar índice para grading_status
        Schema::table('student_responses', function (Blueprint $table) {
            $table->index('grading_status');
        });

        // Agregar índice para plantillas
        Schema::table('assessments', function (Blueprint $table) {
            $table->index('is_template');
        });
    }

    public function down(): void
    {
        Schema::table('student_responses', function (Blueprint $table) {
            $table->dropIndex(['grading_status']);
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['grading_status', 'manual_scores', 'graded_by', 'graded_at']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropIndex(['is_template']);
            $table->dropForeign(['source_template_id']);
            $table->dropColumn(['is_template', 'requires_manual_grading', 'source_template_id']);
        });

        // Revertir course_id a no nullable
        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable(false)->change();
        });
    }
};
