<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class CourseInterestSurveySeeder extends Seeder
{
    /**
     * Likert 1-5 scale options for CIS
     * Based on Keller (2010) - Course Interest Survey
     */
    private const LIKERT_OPTIONS = [
        ['value' => 1, 'label' => 'Strongly Disagree'],
        ['value' => 2, 'label' => 'Disagree'],
        ['value' => 3, 'label' => 'Neutral'],
        ['value' => 4, 'label' => 'Agree'],
        ['value' => 5, 'label' => 'Strongly Agree'],
    ];

    /**
     * CIS items - 34 items
     * Based on Keller, J. M. (2010). Motivational Design for Learning and Performance.
     * ARCS dimensions: Attention, Relevance, Confidence, Satisfaction
     */
    private const QUESTIONS = [
        // Attention (8 items: 1, 4, 10, 15, 21, 24, 26, 29)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The instructor knows how to make us feel enthusiastic about the subject matter of this course.'],
        ['id' => '4', 'item_number' => 4, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'The classes in this course are boring.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The instructor uses an interesting variety of teaching techniques.'],
        ['id' => '15', 'item_number' => 15, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'The instructional materials for this course are boring.'],
        ['id' => '21', 'item_number' => 21, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The variety of activities, assignments, examples, etc., helped keep my attention on the course.'],
        ['id' => '24', 'item_number' => 24, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'My curiosity was often stimulated by the questions asked and the problems given in this course.'],
        ['id' => '26', 'item_number' => 26, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'I often daydreamed during the classes in this course.'],
        ['id' => '29', 'item_number' => 29, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The way the information is presented helped me stay attentive.'],

        // Relevance (9 items: 2, 5, 8, 13, 20, 22, 23, 25, 28)
        ['id' => '2', 'item_number' => 2, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The things I am learning in this course will be useful to me.'],
        ['id' => '5', 'item_number' => 5, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The instructor makes the course content seem important.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'relevance', 'reverse' => true,
         'question' => 'I cannot see how the content of this course is related to anything I already know.'],
        ['id' => '13', 'item_number' => 13, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'In this course, I try to relate the content to my personal goals.'],
        ['id' => '20', 'item_number' => 20, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The content of this course relates to my expectations and goals.'],
        ['id' => '22', 'item_number' => 22, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The content of this course will be useful to me.'],
        ['id' => '23', 'item_number' => 23, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'I can relate the content of this course to things I have seen, done, or thought about in my life.'],
        ['id' => '25', 'item_number' => 25, 'dimension' => 'relevance', 'reverse' => true,
         'question' => 'The content of this course will not be useful to me.'],
        ['id' => '28', 'item_number' => 28, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The personal value of this content makes me want to continue learning about it.'],

        // Confidence (8 items: 3, 6, 9, 11, 17, 27, 30, 34)
        ['id' => '3', 'item_number' => 3, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'I feel confident that I will do well in this course.'],
        ['id' => '6', 'item_number' => 6, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'It is difficult to predict what kind of grade the instructor will give my assignments in this course.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Whether I study or not doesn\'t make any difference in this course.'],
        ['id' => '11', 'item_number' => 11, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'I can adequately understand the materials in this course.'],
        ['id' => '17', 'item_number' => 17, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'It was easy for me to understand what was expected of me in this course.'],
        ['id' => '27', 'item_number' => 27, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'I really cannot understand a good portion of the course material.'],
        ['id' => '30', 'item_number' => 30, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'The amount of work I have to do is appropriate for this type of course.'],
        ['id' => '34', 'item_number' => 34, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'After working on the course activities, I felt confident that I could pass the content evaluations.'],

        // Satisfaction (9 items: 7, 12, 14, 16, 18, 19, 31, 32, 33)
        ['id' => '7', 'item_number' => 7, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'The instructor makes the subject matter of this course enjoyable.'],
        ['id' => '12', 'item_number' => 12, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I felt good about successfully completing this course.'],
        ['id' => '14', 'item_number' => 14, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I enjoyed this course so much that I would like to know more about this subject.'],
        ['id' => '16', 'item_number' => 16, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I enjoyed this course so much that I would like other courses to be taught in a similar way.'],
        ['id' => '18', 'item_number' => 18, 'dimension' => 'satisfaction', 'reverse' => true,
         'question' => 'I did not like this course and do not want to take it again.'],
        ['id' => '19', 'item_number' => 19, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I felt a sense of satisfaction when I was learning.'],
        ['id' => '31', 'item_number' => 31, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I felt good about completing the assignments in this course.'],
        ['id' => '32', 'item_number' => 32, 'dimension' => 'satisfaction', 'reverse' => true,
         'question' => 'It was a waste of time taking this course.'],
        ['id' => '33', 'item_number' => 33, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'The instructor was interested in having students learn in this course.'],
    ];

    public function run(): void
    {
        $questions = collect(self::QUESTIONS)->map(function ($q) {
            return [
                'id' => $q['id'],
                'type' => 'likert',
                'question' => $q['question'],
                'dimension' => $q['dimension'],
                'item_number' => $q['item_number'],
                'options' => self::LIKERT_OPTIONS,
            ];
        })->sortBy('item_number')->values()->toArray();

        $reverseItems = collect(self::QUESTIONS)
            ->filter(fn($q) => $q['reverse'])
            ->pluck('item_number')
            ->values()
            ->toArray();

        Assessment::updateOrCreate(
            [
                'is_template' => true,
                'assessment_type' => 'course_interest',
            ],
            [
                'course_id' => null,
                'title' => 'Course Interest Survey (CIS)',
                'description' => 'Course Interest Survey based on Keller\'s ARCS model (2010). Measures four motivational dimensions: Attention, Relevance, Confidence, and Satisfaction. 34 items on a Likert scale from 1 to 5.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Keller, J. M. (2010). Motivational Design for Learning and Performance: The ARCS Model Approach.',
                    'scale_type' => 'likert_5',
                    'scale_anchors' => [
                        'min' => 'Strongly Disagree (1)',
                        'max' => 'Strongly Agree (5)',
                    ],
                    'reverse_items' => $reverseItems,
                    'dimensions' => [
                        'attention' => ['items' => [1, 4, 10, 15, 21, 24, 26, 29], 'label' => 'Attention'],
                        'relevance' => ['items' => [2, 5, 8, 13, 20, 22, 23, 25, 28], 'label' => 'Relevance'],
                        'confidence' => ['items' => [3, 6, 9, 11, 17, 27, 30, 34], 'label' => 'Confidence'],
                        'satisfaction' => ['items' => [7, 12, 14, 16, 18, 19, 31, 32, 33], 'label' => 'Satisfaction'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 15,
            ]
        );

        $this->command->info('CIS (Course Interest Survey) template created (34 items)');
    }
}
