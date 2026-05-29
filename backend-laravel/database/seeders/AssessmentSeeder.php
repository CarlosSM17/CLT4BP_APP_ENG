<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first course for examples
        $course = Course::first();

        if (!$course) {
            $this->command->info('No courses available. Run CourseSeeder first.');
            return;
        }

        // Initial Recall Assessment
        Assessment::create([
            'course_id' => $course->id,
            'assessment_type' => 'recall_initial',
            'title' => 'Prior Knowledge Assessment',
            'description' => 'Assess your knowledge before starting the course',
            'questions' => [
                [
                    'id' => '1',
                    'type' => 'multiple_choice',
                    'question' => 'What is an algorithm?',
                    'options' => [
                        'A sequence of instructions to solve a problem',
                        'A data type in programming',
                        'A programming language',
                        'A debugging tool'
                    ],
                    'correct_answer' => 0
                ],
                [
                    'id' => '2',
                    'type' => 'multiple_choice',
                    'question' => 'Which of these is NOT a primitive data type?',
                    'options' => [
                        'Integer',
                        'String',
                        'Array',
                        'Boolean'
                    ],
                    'correct_answer' => 2
                ],
                [
                    'id' => '3',
                    'type' => 'text',
                    'question' => 'Describe in your own words what a variable is in programming.',
                    'options' => []
                ]
            ],
            'is_active' => true,
            'time_limit' => 15,
        ]);

        // Initial MSLQ Motivation Assessment
        Assessment::create([
            'course_id' => $course->id,
            'assessment_type' => 'mslq_motivation_initial',
            'title' => 'MSLQ Motivation Questionnaire',
            'description' => 'Assess your motivation and attitude toward learning',
            'questions' => [
                [
                    'id' => '1',
                    'type' => 'likert',
                    'question' => 'I am very interested in the content of this course.',
                    'options' => []
                ],
                [
                    'id' => '2',
                    'type' => 'likert',
                    'question' => 'I believe I can understand even the most difficult concepts presented in this course.',
                    'options' => []
                ],
                [
                    'id' => '3',
                    'type' => 'likert',
                    'question' => 'I expect to do well in this course.',
                    'options' => []
                ],
                [
                    'id' => '4',
                    'type' => 'likert',
                    'question' => 'I think this course is useful for my learning.',
                    'options' => []
                ],
                [
                    'id' => '5',
                    'type' => 'likert',
                    'question' => 'I like the subject matter of this course.',
                    'options' => []
                ]
            ],
            'is_active' => true,
            'time_limit' => 10,
        ]);

        // Cognitive Load Assessment
        Assessment::create([
            'course_id' => $course->id,
            'assessment_type' => 'cognitive_load',
            'title' => 'Cognitive Load Assessment',
            'description' => 'Assess the mental effort during learning',
            'questions' => [
                [
                    'id' => '1',
                    'type' => 'scale',
                    'question' => 'How difficult did you find the content of this lesson? (1=Very easy, 5=Very difficult)',
                    'options' => []
                ],
                [
                    'id' => '2',
                    'type' => 'scale',
                    'question' => 'How much mental effort did you invest? (1=Very little, 5=A lot)',
                    'options' => []
                ],
                [
                    'id' => '3',
                    'type' => 'scale',
                    'question' => 'How confusing did you find the material? (1=Not confusing at all, 5=Very confusing)',
                    'options' => []
                ],
                [
                    'id' => '4',
                    'type' => 'text',
                    'question' => 'Which part of the content did you find most challenging?',
                    'options' => []
                ]
            ],
            'is_active' => false,
            'time_limit' => 5,
        ]);

        $this->command->info('Example assessments created successfully.');
    }
}
