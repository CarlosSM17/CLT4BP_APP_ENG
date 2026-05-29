<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'instructor', 'student'])
                  ->default('student')
                  ->after('email');
            $table->timestamp('last_login')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('last_login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'last_login', 'is_active']);
        });
    }
};
