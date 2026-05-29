<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $constraintName = 'assessments_assessment_type_check';

        DB::statement("ALTER TABLE assessments DROP CONSTRAINT IF EXISTS {$constraintName}");

        DB::statement("ALTER TABLE assessments ADD CONSTRAINT {$constraintName} CHECK (assessment_type::text = ANY (ARRAY[
            'prior_knowledge'::text,
            'recall_initial'::text,
            'comprehension_initial'::text,
            'mslq_motivation_initial'::text,
            'mslq_strategies'::text,
            'recall_final'::text,
            'comprehension_final'::text,
            'cognitive_load'::text,
            'mslq_motivation_final'::text,
            'course_interest'::text,
            'imms'::text
        ]))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $constraintName = 'assessments_assessment_type_check';

        DB::statement("ALTER TABLE assessments DROP CONSTRAINT IF EXISTS {$constraintName}");

        DB::statement("ALTER TABLE assessments ADD CONSTRAINT {$constraintName} CHECK (assessment_type::text = ANY (ARRAY[
            'recall_initial'::text,
            'comprehension_initial'::text,
            'mslq_motivation_initial'::text,
            'mslq_strategies'::text,
            'recall_final'::text,
            'comprehension_final'::text,
            'cognitive_load'::text,
            'mslq_motivation_final'::text,
            'course_interest'::text,
            'imms'::text
        ]))");
    }
};
