<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->json('learning_objectives')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive', 'completed'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index('instructor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
