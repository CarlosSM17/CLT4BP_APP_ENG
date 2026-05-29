<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class CognitiveLoadScaleSeeder extends Seeder
{
    /**
     * 0-10 scale options for cognitive load
     * Based on Leppink et al. (2013)
     */
    private const SCALE_OPTIONS = [
        ['value' => 0, 'label' => 'Not at all'],
        ['value' => 1, 'label' => '1'],
        ['value' => 2, 'label' => '2'],
        ['value' => 3, 'label' => '3'],
        ['value' => 4, 'label' => '4'],
        ['value' => 5, 'label' => '5'],
        ['value' => 6, 'label' => '6'],
        ['value' => 7, 'label' => '7'],
        ['value' => 8, 'label' => '8'],
        ['value' => 9, 'label' => '9'],
        ['value' => 10, 'label' => 'Completely'],
    ];

    /**
     * Cognitive load scale items
     * Based on Leppink, Paas, Van der Vleuten, Van Gog & Van Merriënboer (2013)
     */
    private const QUESTIONS = [
        // Intrinsic Load (items 1-3)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'intrinsic_load',
         'question' => 'The topics covered in the activity were very complex.'],
        ['id' => '2', 'item_number' => 2, 'dimension' => 'intrinsic_load',
         'question' => 'The activity covered concepts that I found very complex.'],
        ['id' => '3', 'item_number' => 3, 'dimension' => 'intrinsic_load',
         'question' => 'The activity covered formulae/procedures that I found very complex.'],

        // Extraneous Load (items 4-6)
        ['id' => '4', 'item_number' => 4, 'dimension' => 'extraneous_load',
         'question' => 'The instructions and/or explanations during the activity were very unclear.'],
        ['id' => '5', 'item_number' => 5, 'dimension' => 'extraneous_load',
         'question' => 'The instructions and/or explanations were, in terms of learning, very ineffective.'],
        ['id' => '6', 'item_number' => 6, 'dimension' => 'extraneous_load',
         'question' => 'The instructions and/or explanations were full of unclear language.'],

        // Germane Load (items 7-10)
        ['id' => '7', 'item_number' => 7, 'dimension' => 'germane_load',
         'question' => 'The activity really enhanced my understanding of the topics covered.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'germane_load',
         'question' => 'The activity really enhanced my knowledge and understanding of concepts.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'germane_load',
         'question' => 'The activity really enhanced my understanding of formulae/procedures.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'germane_load',
         'question' => 'The activity really enhanced my ability to connect the topics covered to what I already knew.'],
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
                'options' => self::SCALE_OPTIONS,
            ];
        })->sortBy('item_number')->values()->toArray();

        Assessment::updateOrCreate(
            [
                'is_template' => true,
                'assessment_type' => 'cognitive_load',
            ],
            [
                'course_id' => null,
                'title' => 'Cognitive Load Scale',
                'description' => 'Cognitive load scale based on Leppink et al. (2013). Measures three types of cognitive load: intrinsic (content complexity), extraneous (instructional quality), and germane (contribution to learning). 10 items on a 0 to 10 scale.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Leppink, J., Paas, F., Van der Vleuten, C. P. M., Van Gog, T., & Van Merriënboer, J. J. G. (2013)',
                    'scale_type' => 'likert_11',
                    'scale_anchors' => [
                        'min' => 'Not at all (0)',
                        'max' => 'Completely (10)',
                    ],
                    'dimensions' => [
                        'intrinsic_load' => ['items' => [1, 2, 3], 'label' => 'Intrinsic Load'],
                        'extraneous_load' => ['items' => [4, 5, 6], 'label' => 'Extraneous Load'],
                        'germane_load' => ['items' => [7, 8, 9, 10], 'label' => 'Germane Load'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 10,
            ]
        );

        $this->command->info('Cognitive Load Scale template created (10 items)');
    }
}
