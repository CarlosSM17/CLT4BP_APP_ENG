<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class ImmsSeeder extends Seeder
{
    /**
     * Likert 1-5 scale options for IMMS
     */
    private const LIKERT_OPTIONS = [
        ['value' => 1, 'label' => 'Strongly Disagree'],
        ['value' => 2, 'label' => 'Disagree'],
        ['value' => 3, 'label' => 'Neutral'],
        ['value' => 4, 'label' => 'Agree'],
        ['value' => 5, 'label' => 'Strongly Agree'],
    ];

    /**
     * IMMS items - 36 items
     * Based on Keller, J. M. (2010). Motivational Design for Learning and Performance.
     * ARCS dimensions: Attention (12), Relevance (9), Confidence (9), Satisfaction (6)
     */
    private const QUESTIONS = [
        // Attention (12 items: 2, 8, 11, 12, 15, 17, 20, 22, 24, 28, 29, 31)
        ['id' => '2', 'item_number' => 2, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'There was something interesting at the beginning of this instruction that attracted my attention.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The instruction was visually appealing.'],
        ['id' => '11', 'item_number' => 11, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The quality of the writing helped to hold my attention.'],
        ['id' => '12', 'item_number' => 12, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'The instruction is so abstract that it was hard to keep my attention on it.'],
        ['id' => '15', 'item_number' => 15, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'The layout or the style of writing of the instructional materials seems boring and unappealing.'],
        ['id' => '17', 'item_number' => 17, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The way the information is arranged on the pages helped me stay attentive.'],
        ['id' => '20', 'item_number' => 20, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'There are things in this instruction that stimulated my curiosity.'],
        ['id' => '22', 'item_number' => 22, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'The amount of repetition in this instruction caused me to feel bored at times.'],
        ['id' => '24', 'item_number' => 24, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'I learned some things that were surprising or unexpected.'],
        ['id' => '28', 'item_number' => 28, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'The variety of reading passages, exercises, illustrations, etc., helped me maintain my attention on the material.'],
        ['id' => '29', 'item_number' => 29, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'The writing style is boring.'],
        ['id' => '31', 'item_number' => 31, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'There is so much content that it is hard to identify and remember the key points.'],

        // Relevance (9 items: 6, 9, 10, 16, 18, 23, 26, 30, 33)
        ['id' => '6', 'item_number' => 6, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'It is clear to me how the content of this material is related to things I already know.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'There were stories, pictures, or examples that showed me how this material could be important to some people.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Completing this instruction successfully was important to me.'],
        ['id' => '16', 'item_number' => 16, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The content of this material is relevant to my interests.'],
        ['id' => '18', 'item_number' => 18, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'There are explanations or examples of how people use the knowledge in this material.'],
        ['id' => '23', 'item_number' => 23, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The content and style of writing in this material convey the impression that its content is worth knowing.'],
        ['id' => '26', 'item_number' => 26, 'dimension' => 'relevance', 'reverse' => true,
         'question' => 'This material was not relevant to my needs because I already knew most of it.'],
        ['id' => '30', 'item_number' => 30, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'I could relate the content of this material to things I have seen, done, or thought about in my own life.'],
        ['id' => '33', 'item_number' => 33, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'The content of this material will be useful to me.'],

        // Confidence (9 items: 1, 3, 4, 7, 13, 19, 25, 34, 35)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'When I first looked at this instruction, I had the impression that it would be easy for me.'],
        ['id' => '3', 'item_number' => 3, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'This material was more difficult to understand than I would like for it to be.'],
        ['id' => '4', 'item_number' => 4, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'After reading the introductory information, I felt confident that I knew what I was supposed to learn from this material.'],
        ['id' => '7', 'item_number' => 7, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Many of the pages had so much information that it was hard to identify and remember the important points.'],
        ['id' => '13', 'item_number' => 13, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'As I worked through this material, I was confident that I could learn the content.'],
        ['id' => '19', 'item_number' => 19, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'The exercises in this material were too difficult.'],
        ['id' => '25', 'item_number' => 25, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'After working through some of this material, I was confident that I would be able to pass a test on it.'],
        ['id' => '34', 'item_number' => 34, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'I could understand the study material quite well.'],
        ['id' => '35', 'item_number' => 35, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'The good organization of the content helped me be confident that I would learn this material.'],

        // Satisfaction (6 items: 5, 14, 21, 27, 32, 36)
        ['id' => '5', 'item_number' => 5, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Completing the exercises in this material gave me a satisfying feeling of accomplishment.'],
        ['id' => '14', 'item_number' => 14, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I enjoyed this material so much that I would like to know more about this topic.'],
        ['id' => '21', 'item_number' => 21, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I enjoyed studying this material.'],
        ['id' => '27', 'item_number' => 27, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'The feedback after the exercises, or other feedback in the material, helped me feel rewarded for my effort.'],
        ['id' => '32', 'item_number' => 32, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'I felt good about successfully completing this material.'],
        ['id' => '36', 'item_number' => 36, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'It was a pleasure to work with such a well-designed instruction.'],
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
                'assessment_type' => 'imms',
            ],
            [
                'course_id' => null,
                'title' => 'Instructional Materials Motivation Survey (IMMS)',
                'description' => 'Instructional Materials Motivation Survey based on Keller\'s ARCS model (2010). Evaluates the motivation generated by instructional materials across four dimensions: Attention, Relevance, Confidence, and Satisfaction. 36 items on a Likert scale from 1 to 5.',
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
                        'attention' => ['items' => [2, 8, 11, 12, 15, 17, 20, 22, 24, 28, 29, 31], 'label' => 'Attention'],
                        'relevance' => ['items' => [6, 9, 10, 16, 18, 23, 26, 30, 33], 'label' => 'Relevance'],
                        'confidence' => ['items' => [1, 3, 4, 7, 13, 19, 25, 34, 35], 'label' => 'Confidence'],
                        'satisfaction' => ['items' => [5, 14, 21, 27, 32, 36], 'label' => 'Satisfaction'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 15,
            ]
        );

        $this->command->info('IMMS (Instructional Materials Motivation Survey) template created (36 items)');
    }
}
