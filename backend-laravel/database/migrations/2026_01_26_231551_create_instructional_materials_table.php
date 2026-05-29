<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('instructional_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('material_type', [
                'learning_tasks',
                'support_info',
                'procedural_info',
                'verbal_protocols',
                'example'
            ]);
            $table->enum('target_type', ['individual', 'group']);
            $table->foreignId('target_student_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->json('content');
            $table->boolean('is_active')->default(false);
            $table->integer('timer_seconds')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'material_type']);
            $table->index(['course_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('instructional_materials');
    }
};
