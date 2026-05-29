<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class RecallFinalSeeder extends Seeder
{
    public function run(): void
    {
        Assessment::updateOrCreate(
            [
                'is_template' => true,
                'assessment_type' => 'recall_final',
            ],
            [
                'course_id' => null,
                'title' => 'Final Recall Test (Post-test)',
                'description' => 'Template for the final recall test. The instructor should customize the questions according to the specific content of their course. Assesses retention of key concepts at the end of the course. Allows comparison with the initial recall test.',
                'questions' => [
                    [
                        'id' => '1',
                        'type' => 'multiple_choice',
                        'question' => '[Example question - Customize] What is the correct definition of concept X?',
                        'options' => [
                            'Correct answer (modify for your course)',
                            'Distractor 1 (modify for your course)',
                            'Distractor 2 (modify for your course)',
                            'Distractor 3 (modify for your course)',
                        ],
                        'correct_answer' => 0,
                    ],
                    [
                        'id' => '2',
                        'type' => 'multiple_choice',
                        'question' => '[Example question - Customize] Which concept best describes process Y?',
                        'options' => [
                            'Distractor 1',
                            'Correct answer (modify)',
                            'Distractor 2',
                            'Distractor 3',
                        ],
                        'correct_answer' => 1,
                    ],
                    [
                        'id' => '3',
                        'type' => 'multiple_choice',
                        'question' => '[Example question - Customize] What are the main components of Z?',
                        'options' => [
                            'Distractor 1',
                            'Distractor 2',
                            'Correct answer (modify)',
                            'Distractor 3',
                        ],
                        'correct_answer' => 2,
                    ],
                    [
                        'id' => '4',
                        'type' => 'multiple_choice',
                        'question' => '[Example question - Customize] What does method W consist of?',
                        'options' => [
                            'Distractor 1',
                            'Distractor 2',
                            'Distractor 3',
                            'Correct answer (modify)',
                        ],
                        'correct_answer' => 3,
                    ],
                    [
                        'id' => '5',
                        'type' => 'multiple_choice',
                        'question' => '[Example question - Customize] Which of the following statements is correct about V?',
                        'options' => [
                            'Correct answer (modify)',
                            'Distractor 1',
                            'Distractor 2',
                            'Distractor 3',
                        ],
                        'correct_answer' => 0,
                    ],
                ],
                'config' => [
                    'version' => '1.0',
                    'is_post_test' => true,
                    'pre_test_type' => 'recall_initial',
                    'instructions' => 'The instructor should replace the example questions with course-specific questions that assess retention of key concepts.',
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 15,
            ]
        );

        $this->command->info('Final Recall Test template created (5 example questions)');
    }
}
