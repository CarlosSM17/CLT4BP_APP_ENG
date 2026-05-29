<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clt_effects_selection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->json('selected_effects');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('course_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clt_effects_selection');
    }
};
