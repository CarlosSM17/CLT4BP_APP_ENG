<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class MslqMotivationFinalSeeder extends Seeder
{
    /**
     * Likert 1-7 scale options for MSLQ
     * Identical to MslqQuestionnaireSeeder
     */
    private const LIKERT_OPTIONS = [
        ['value' => 1, 'label' => 'Not at all true of me'],
        ['value' => 2, 'label' => 'Slightly true of me'],
        ['value' => 3, 'label' => 'Somewhat true of me'],
        ['value' => 4, 'label' => 'Neither true nor untrue of me'],
        ['value' => 5, 'label' => 'Moderately true of me'],
        ['value' => 6, 'label' => 'Very true of me'],
        ['value' => 7, 'label' => 'Completely true of me'],
    ];

    /**
     * Same 31 motivation questions as the initial MSLQ
     * Enables pre/post comparison
     */
    private const MOTIVATION_QUESTIONS = [
        // Intrinsic Goal Orientation (items 1, 16, 22, 24)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'In a class like this, I prefer course material that really challenges me so I can learn new things.'],
        ['id' => '16', 'item_number' => 16, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'In a class like this, I prefer course material that arouses my curiosity, even if it is difficult to learn.'],
        ['id' => '22', 'item_number' => 22, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'The most satisfying thing for me in this course is trying to understand the content as thoroughly as possible.'],
        ['id' => '24', 'item_number' => 24, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'When I have the opportunity in this class, I choose course assignments that I can learn from even if they don\'t guarantee a good grade.'],

        // Extrinsic Goal Orientation (items 7, 11, 13, 30)
        ['id' => '7', 'item_number' => 7, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'Getting a good grade in this class is the most satisfying thing for me right now.'],
        ['id' => '11', 'item_number' => 11, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'The most important thing for me right now is improving my overall grade point average, so my main concern in this class is getting a good grade.'],
        ['id' => '13', 'item_number' => 13, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'If I can, I want to get better grades in this class than most of the other students.'],
        ['id' => '30', 'item_number' => 30, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'I want to do well in this class because it is important to show my ability to my family, friends, employer, or others.'],

        // Task Value (items 4, 10, 17, 23, 26, 27)
        ['id' => '4', 'item_number' => 4, 'dimension' => 'task_value',
         'question' => 'I think I will be able to use what I learn in this course in other courses.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'task_value',
         'question' => 'It is important for me to learn the course material in this class.'],
        ['id' => '17', 'item_number' => 17, 'dimension' => 'task_value',
         'question' => 'I am very interested in the content area of this course.'],
        ['id' => '23', 'item_number' => 23, 'dimension' => 'task_value',
         'question' => 'I think the course material in this class is useful for me to learn.'],
        ['id' => '26', 'item_number' => 26, 'dimension' => 'task_value',
         'question' => 'I like the subject matter of this course.'],
        ['id' => '27', 'item_number' => 27, 'dimension' => 'task_value',
         'question' => 'Understanding the subject matter of this course is very important to me.'],

        // Control Beliefs (items 2, 9, 18, 25)
        ['id' => '2', 'item_number' => 2, 'dimension' => 'control_beliefs',
         'question' => 'If I study in appropriate ways, then I will be able to learn the material in this course.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'control_beliefs',
         'question' => 'It is my own fault if I don\'t learn the material in this course.'],
        ['id' => '18', 'item_number' => 18, 'dimension' => 'control_beliefs',
         'question' => 'If I try hard enough, then I will understand the course material.'],
        ['id' => '25', 'item_number' => 25, 'dimension' => 'control_beliefs',
         'question' => 'If I don\'t understand the course material, it is because I didn\'t try hard enough.'],

        // Self-Efficacy (items 5, 6, 12, 15, 20, 21, 29, 31)
        ['id' => '5', 'item_number' => 5, 'dimension' => 'self_efficacy',
         'question' => 'I believe I will receive an excellent grade in this class.'],
        ['id' => '6', 'item_number' => 6, 'dimension' => 'self_efficacy',
         'question' => 'I\'m confident I can understand the most difficult material presented in the readings for this course.'],
        ['id' => '12', 'item_number' => 12, 'dimension' => 'self_efficacy',
         'question' => 'I\'m confident I can learn the basic concepts taught in this course.'],
        ['id' => '15', 'item_number' => 15, 'dimension' => 'self_efficacy',
         'question' => 'I\'m confident I can understand the most complex material presented by the instructor in this course.'],
        ['id' => '20', 'item_number' => 20, 'dimension' => 'self_efficacy',
         'question' => 'I\'m confident I can do an excellent job on the assignments and tests in this course.'],
        ['id' => '21', 'item_number' => 21, 'dimension' => 'self_efficacy',
         'question' => 'I expect to do well in this class.'],
        ['id' => '29', 'item_number' => 29, 'dimension' => 'self_efficacy',
         'question' => 'I\'m certain I can master the skills being taught in this class.'],
        ['id' => '31', 'item_number' => 31, 'dimension' => 'self_efficacy',
         'question' => 'Considering the difficulty of this course, the teacher, and my skills, I think I will do well in this class.'],

        // Test Anxiety (items 3, 8, 14, 19, 28)
        ['id' => '3', 'item_number' => 3, 'dimension' => 'test_anxiety',
         'question' => 'When I take a test I think about how poorly I am doing compared with other students.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'test_anxiety',
         'question' => 'When I take an exam I think about items on other parts of the exam I can\'t answer.'],
        ['id' => '14', 'item_number' => 14, 'dimension' => 'test_anxiety',
         'question' => 'When I take tests I think of the consequences of failing.'],
        ['id' => '19', 'item_number' => 19, 'dimension' => 'test_anxiety',
         'question' => 'I have an uneasy, upset feeling when I take an exam.'],
        ['id' => '28', 'item_number' => 28, 'dimension' => 'test_anxiety',
         'question' => 'I feel my heart beating fast when I take an exam.'],
    ];

    public function run(): void
    {
        $questions = collect(self::MOTIVATION_QUESTIONS)->map(function ($q) {
            return [
                'id' => $q['id'],
                'type' => 'likert',
                'question' => $q['question'],
                'dimension' => $q['dimension'],
                'item_number' => $q['item_number'],
                'options' => self::LIKERT_OPTIONS,
            ];
        })->sortBy('item_number')->values()->toArray();

        Assessment::updateOrCreate(
            [
                'is_template' => true,
                'assessment_type' => 'mslq_motivation_final',
            ],
            [
                'course_id' => null,
                'title' => 'MSLQ - Motivation Scales (Post-test)',
                'description' => 'Motivated Strategies for Learning Questionnaire (MSLQ) - Motivation Section (Post-test). Same 31 items as the initial version to enable pre/post comparison. Measures: intrinsic and extrinsic goal orientation, task value, control beliefs, self-efficacy, and test anxiety.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Pintrich, P. R., Smith, D. A., Garcia, T., & McKeachie, W. J. (1991)',
                    'scale_type' => 'likert_7',
                    'scale_anchors' => [
                        'min' => 'Not at all true of me',
                        'max' => 'Completely true of me',
                    ],
                    'is_post_test' => true,
                    'pre_test_type' => 'mslq_motivation_initial',
                    'dimensions' => [
                        'intrinsic_goal_orientation' => ['items' => [1, 16, 22, 24], 'label' => 'Intrinsic Goal Orientation'],
                        'extrinsic_goal_orientation' => ['items' => [7, 11, 13, 30], 'label' => 'Extrinsic Goal Orientation'],
                        'task_value' => ['items' => [4, 10, 17, 23, 26, 27], 'label' => 'Task Value'],
                        'control_beliefs' => ['items' => [2, 9, 18, 25], 'label' => 'Control Beliefs'],
                        'self_efficacy' => ['items' => [5, 6, 12, 15, 20, 21, 29, 31], 'label' => 'Self-Efficacy'],
                        'test_anxiety' => ['items' => [3, 8, 14, 19, 28], 'label' => 'Test Anxiety'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 20,
            ]
        );

        $this->command->info('MSLQ Motivation Final (Post-test) template created (31 items)');
    }
}
