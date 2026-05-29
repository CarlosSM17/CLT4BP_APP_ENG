<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class MslqQuestionnaireSeeder extends Seeder
{
    /**
     * Likert 1-7 scale options for MSLQ
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
     * Motivation Questions (Items 1-31)
     * Based on Pintrich et al. (1991) - MSLQ Manual
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

        // Self-Efficacy for Learning and Performance (items 5, 6, 12, 15, 20, 21, 29, 31)
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

    /**
     * Learning Strategies Questions (Items 32-81)
     */
    private const STRATEGIES_QUESTIONS = [
        // Organization (items 32, 42, 49, 63)
        ['id' => '32', 'item_number' => 32, 'dimension' => 'organization',
         'question' => 'When I study the readings for this course, I outline the material to help me organize my thoughts.'],
        ['id' => '42', 'item_number' => 42, 'dimension' => 'organization',
         'question' => 'When I study for this course, I go over my class notes and make an outline of important concepts.'],
        ['id' => '49', 'item_number' => 49, 'dimension' => 'organization',
         'question' => 'I make simple charts, diagrams, or tables to help me organize course material.'],
        ['id' => '63', 'item_number' => 63, 'dimension' => 'organization',
         'question' => 'When I study for this course, I go over the readings and my class notes and try to find the most important ideas.'],

        // Metacognitive Self-Regulation (items 33, 36, 41, 44, 54, 55, 56, 57, 61, 76, 78, 79)
        ['id' => '33', 'item_number' => 33, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'During class time I often miss important points because I\'m thinking of other things.'],
        ['id' => '36', 'item_number' => 36, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'When reading for this class, I make up questions to help focus my reading.'],
        ['id' => '41', 'item_number' => 41, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'When I become confused about something I\'m reading for this class, I go back and try to figure it out.'],
        ['id' => '44', 'item_number' => 44, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'If course materials are difficult to understand, I change the way I read the material.'],
        ['id' => '54', 'item_number' => 54, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Before I study new course material thoroughly, I often skim it to see how it is organized.'],
        ['id' => '55', 'item_number' => 55, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'I ask myself questions to make sure I understand the material I have been studying in this class.'],
        ['id' => '56', 'item_number' => 56, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'I try to change the way I study in order to fit the course requirements and the instructor\'s teaching style.'],
        ['id' => '57', 'item_number' => 57, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'I often find that I have been reading for this class but don\'t know what it was all about.'],
        ['id' => '61', 'item_number' => 61, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'I try to think through a topic and decide what I am supposed to learn from it rather than just reading it over when I study for this class.'],
        ['id' => '76', 'item_number' => 76, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'When studying for this class, I set goals for myself in order to direct my activities in each study period.'],
        ['id' => '78', 'item_number' => 78, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'When studying for this class, I often try to explain the material to a classmate or friend.'],
        ['id' => '79', 'item_number' => 79, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'I usually study in a place where I can concentrate on my course work.'],

        // Peer Learning (items 34, 45, 50)
        ['id' => '34', 'item_number' => 34, 'dimension' => 'peer_learning',
         'question' => 'When studying for this course, I often try to explain the material to a classmate or friend.'],
        ['id' => '45', 'item_number' => 45, 'dimension' => 'peer_learning',
         'question' => 'I try to work with other students from this class to complete the course assignments.'],
        ['id' => '50', 'item_number' => 50, 'dimension' => 'peer_learning',
         'question' => 'When studying for this course, I often set aside time to discuss course material with a group of students from the class.'],

        // Time and Study Environment Management (items 35, 43, 52, 65, 70, 73, 77, 80)
        ['id' => '35', 'item_number' => 35, 'dimension' => 'time_management',
         'question' => 'I usually study in a place where I can concentrate on my course work.'],
        ['id' => '43', 'item_number' => 43, 'dimension' => 'time_management',
         'question' => 'I make good use of my study time for this course.'],
        ['id' => '52', 'item_number' => 52, 'dimension' => 'time_management',
         'question' => 'I find it hard to stick to a study schedule.'],
        ['id' => '65', 'item_number' => 65, 'dimension' => 'time_management',
         'question' => 'I have a regular place set aside for studying.'],
        ['id' => '70', 'item_number' => 70, 'dimension' => 'time_management',
         'question' => 'I make sure that I keep up with the weekly readings and assignments for this course.'],
        ['id' => '73', 'item_number' => 73, 'dimension' => 'time_management',
         'question' => 'I attend class regularly.'],
        ['id' => '77', 'item_number' => 77, 'dimension' => 'time_management',
         'question' => 'I often find that I don\'t spend very much time on this course because of other activities.'],
        ['id' => '80', 'item_number' => 80, 'dimension' => 'time_management',
         'question' => 'I rarely find time to review my notes or the assigned readings before an exam.'],

        // Effort Regulation (items 37, 48, 60, 74)
        ['id' => '37', 'item_number' => 37, 'dimension' => 'effort_regulation',
         'question' => 'I often feel so lazy or bored when I study for this class that I quit before I finish what I planned to do.'],
        ['id' => '48', 'item_number' => 48, 'dimension' => 'effort_regulation',
         'question' => 'I work hard to do well in this class even if I don\'t like what we are doing.'],
        ['id' => '60', 'item_number' => 60, 'dimension' => 'effort_regulation',
         'question' => 'When course work is difficult, I give up or only study the easy parts.'],
        ['id' => '74', 'item_number' => 74, 'dimension' => 'effort_regulation',
         'question' => 'Even when course materials are dull and uninteresting, I manage to keep working until I finish.'],

        // Critical Thinking (items 38, 47, 51, 66, 71)
        ['id' => '38', 'item_number' => 38, 'dimension' => 'critical_thinking',
         'question' => 'I often find myself questioning things I hear or read in this course to decide if I find them convincing.'],
        ['id' => '47', 'item_number' => 47, 'dimension' => 'critical_thinking',
         'question' => 'When a theory, interpretation, or conclusion is presented in class or in the readings, I try to decide if there is good evidence to support it.'],
        ['id' => '51', 'item_number' => 51, 'dimension' => 'critical_thinking',
         'question' => 'I treat the course material as a starting point and try to develop my own ideas about it.'],
        ['id' => '66', 'item_number' => 66, 'dimension' => 'critical_thinking',
         'question' => 'I try to play around with ideas of my own related to what I am learning in this course.'],
        ['id' => '71', 'item_number' => 71, 'dimension' => 'critical_thinking',
         'question' => 'Whenever I read or hear an assertion or conclusion in this class, I think about possible alternatives.'],

        // Rehearsal (items 39, 46, 59, 72)
        ['id' => '39', 'item_number' => 39, 'dimension' => 'rehearsal',
         'question' => 'When I study for this class, I practice saying the material to myself over and over.'],
        ['id' => '46', 'item_number' => 46, 'dimension' => 'rehearsal',
         'question' => 'When studying for this class, I read my class notes and the course readings over and over again.'],
        ['id' => '59', 'item_number' => 59, 'dimension' => 'rehearsal',
         'question' => 'I memorize key words to remind me of important concepts in this class.'],
        ['id' => '72', 'item_number' => 72, 'dimension' => 'rehearsal',
         'question' => 'I make lists of important terms for this course and memorize the lists.'],

        // Help Seeking (items 40, 58, 68, 75)
        ['id' => '40', 'item_number' => 40, 'dimension' => 'help_seeking',
         'question' => 'Even if I have trouble learning the material in this class, I try to do the work on my own, without help from anyone.'],
        ['id' => '58', 'item_number' => 58, 'dimension' => 'help_seeking',
         'question' => 'I ask the instructor to clarify concepts I don\'t understand well.'],
        ['id' => '68', 'item_number' => 68, 'dimension' => 'help_seeking',
         'question' => 'When I can\'t understand the material in this course, I ask another student in this class for help.'],
        ['id' => '75', 'item_number' => 75, 'dimension' => 'help_seeking',
         'question' => 'I try to identify students in this class whom I can ask for help if necessary.'],

        // Elaboration (items 53, 62, 64, 67, 69, 81)
        ['id' => '53', 'item_number' => 53, 'dimension' => 'elaboration',
         'question' => 'When studying for this class, I pull together information from different sources, such as lectures, readings, and discussions.'],
        ['id' => '62', 'item_number' => 62, 'dimension' => 'elaboration',
         'question' => 'I try to relate ideas in this subject to those in other courses whenever possible.'],
        ['id' => '64', 'item_number' => 64, 'dimension' => 'elaboration',
         'question' => 'When I read material for this class, I try to relate it to what I already know.'],
        ['id' => '67', 'item_number' => 67, 'dimension' => 'elaboration',
         'question' => 'When studying for this class, I write brief summaries of the main ideas from the readings and my class notes.'],
        ['id' => '69', 'item_number' => 69, 'dimension' => 'elaboration',
         'question' => 'I try to understand the material in this class by making connections between the readings and the concepts from the lectures.'],
        ['id' => '81', 'item_number' => 81, 'dimension' => 'elaboration',
         'question' => 'I try to apply ideas from course readings in other class activities such as lecture and discussion.'],
    ];

    public function run(): void
    {
        // Create MSLQ Motivation template
        $this->createMotivationTemplate();

        // Create MSLQ Strategies template
        $this->createStrategiesTemplate();

        $this->command->info('MSLQ templates created successfully:');
        $this->command->info('- MSLQ Motivation (31 items)');
        $this->command->info('- MSLQ Learning Strategies (50 items)');
    }

    private function createMotivationTemplate(): void
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
                'assessment_type' => 'mslq_motivation_initial',
            ],
            [
                'course_id' => null,
                'title' => 'MSLQ - Motivation Scales',
                'description' => 'Motivated Strategies for Learning Questionnaire (MSLQ) - Motivation Section. Developed by Pintrich et al. (1991). Includes 31 items measuring: intrinsic and extrinsic goal orientation, task value, control beliefs, self-efficacy, and test anxiety.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Pintrich, P. R., Smith, D. A., Garcia, T., & McKeachie, W. J. (1991)',
                    'scale_type' => 'likert_7',
                    'scale_anchors' => [
                        'min' => 'Not at all true of me',
                        'max' => 'Completely true of me',
                    ],
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
    }

    private function createStrategiesTemplate(): void
    {
        $questions = collect(self::STRATEGIES_QUESTIONS)->map(function ($q) {
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
                'assessment_type' => 'mslq_strategies',
            ],
            [
                'course_id' => null,
                'title' => 'MSLQ - Learning Strategies Scales',
                'description' => 'Motivated Strategies for Learning Questionnaire (MSLQ) - Learning Strategies Section. Developed by Pintrich et al. (1991). Includes 50 items measuring cognitive strategies (rehearsal, elaboration, organization, critical thinking), metacognitive strategies (self-regulation), and resource management strategies (time management, effort regulation, peer learning, help seeking).',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Pintrich, P. R., Smith, D. A., Garcia, T., & McKeachie, W. J. (1991)',
                    'scale_type' => 'likert_7',
                    'scale_anchors' => [
                        'min' => 'Not at all true of me',
                        'max' => 'Completely true of me',
                    ],
                    'dimensions' => [
                        'rehearsal' => ['items' => [39, 46, 59, 72], 'label' => 'Rehearsal'],
                        'elaboration' => ['items' => [53, 62, 64, 67, 69, 81], 'label' => 'Elaboration'],
                        'organization' => ['items' => [32, 42, 49, 63], 'label' => 'Organization'],
                        'critical_thinking' => ['items' => [38, 47, 51, 66, 71], 'label' => 'Critical Thinking'],
                        'metacognitive_self_regulation' => ['items' => [33, 36, 41, 44, 54, 55, 56, 57, 61, 76, 78, 79], 'label' => 'Metacognitive Self-Regulation'],
                        'time_management' => ['items' => [35, 43, 52, 65, 70, 73, 77, 80], 'label' => 'Time Management'],
                        'effort_regulation' => ['items' => [37, 48, 60, 74], 'label' => 'Effort Regulation'],
                        'peer_learning' => ['items' => [34, 45, 50], 'label' => 'Peer Learning'],
                        'help_seeking' => ['items' => [40, 58, 68, 75], 'label' => 'Help Seeking'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 30,
            ]
        );
    }
}
