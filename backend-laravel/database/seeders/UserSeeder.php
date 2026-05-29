<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@clt4bp.com'],
            [
                'name' => 'Admin CLT4BP',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'instructor@clt4bp.com'],
            [
                'name' => 'Instructor Demo',
                'password' => Hash::make('instructor123'),
                'role' => 'instructor',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'estudiante@clt4bp.com'],
            [
                'name' => 'Estudiante Demo',
                'password' => Hash::make('estudiante123'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );
    }
}
