<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('instructional_materials')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamps();

            $table->index(['material_id', 'student_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_access_logs');
    }
};
