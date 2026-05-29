<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class ComprehensionFinalSeeder extends Seeder
{
    public function run(): void
    {
        Assessment::updateOrCreate(
            [
                'is_template' => true,
                'assessment_type' => 'comprehension_final',
            ],
            [
                'course_id' => null,
                'title' => 'Final Comprehension Test (Post-test)',
                'description' => 'Template for the final comprehension test. The instructor should customize the questions according to the course content. Assesses deep understanding (application, analysis) at the end of the course. Includes multiple-choice and open-ended questions. Allows comparison with the initial comprehension test.',
                'questions' => [
                    [
                        'id' => '1',
                        'type' => 'multiple_choice',
                        'question' => '[Customize] Given the following scenario, what would be the best solution applying concept X?',
                        'options' => [
                            'Correct answer (modify for your course)',
                            'Plausible distractor 1',
                            'Plausible distractor 2',
                            'Plausible distractor 3',
                        ],
                        'correct_answer' => 0,
                    ],
                    [
                        'id' => '2',
                        'type' => 'multiple_choice',
                        'question' => '[Customize] What result would be obtained by applying procedure Y in the described situation?',
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
                        'question' => '[Customize] What is the main difference between approach A and approach B for solving problem Z?',
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
                        'type' => 'text',
                        'question' => '[Customize] Explain in your own words how concept X is applied to solve a problem of type Y. Provide a concrete example.',
                        'options' => [],
                    ],
                    [
                        'id' => '5',
                        'type' => 'text',
                        'question' => '[Customize] Analyze the advantages and disadvantages of using method W compared to method V. In what situations would you recommend each?',
                        'options' => [],
                    ],
                ],
                'config' => [
                    'version' => '1.0',
                    'is_post_test' => true,
                    'pre_test_type' => 'comprehension_initial',
                    'instructions' => 'The instructor should replace the example questions with course-specific comprehension questions. It is recommended to maintain a mix of multiple-choice questions (application) and open-ended questions (analysis and synthesis).',
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => true,
                'time_limit' => 20,
            ]
        );

        $this->command->info('Final Comprehension Test template created (5 example questions)');
    }
}
