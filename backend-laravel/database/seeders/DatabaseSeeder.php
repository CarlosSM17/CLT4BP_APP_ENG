<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MslqQuestionnaireSeeder::class,
            CognitiveLoadScaleSeeder::class,
            CourseInterestSurveySeeder::class,
            ImmsSeeder::class,
            MslqMotivationFinalSeeder::class,
            RecallFinalSeeder::class,
            ComprehensionFinalSeeder::class,
        ]);
    }
}
