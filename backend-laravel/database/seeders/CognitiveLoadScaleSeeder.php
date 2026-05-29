<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class CognitiveLoadScaleSeeder extends Seeder
{
    /**
     * Opciones de escala 0-10 para carga cognitiva
     * Basado en Leppink et al. (2013)
     */
    private const SCALE_OPTIONS = [
        ['value' => 0, 'label' => 'Nada en absoluto'],
        ['value' => 1, 'label' => '1'],
        ['value' => 2, 'label' => '2'],
        ['value' => 3, 'label' => '3'],
        ['value' => 4, 'label' => '4'],
        ['value' => 5, 'label' => '5'],
        ['value' => 6, 'label' => '6'],
        ['value' => 7, 'label' => '7'],
        ['value' => 8, 'label' => '8'],
        ['value' => 9, 'label' => '9'],
        ['value' => 10, 'label' => 'Completamente'],
    ];

    /**
     * Items de la escala de carga cognitiva
     * Basado en Leppink, Paas, Van der Vleuten, Van Gog & Van Merriënboer (2013)
     */
    private const QUESTIONS = [
        // Carga Intrínseca (items 1-3)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'intrinsic_load',
         'question' => 'Los temas cubiertos en la actividad fueron muy complejos.'],
        ['id' => '2', 'item_number' => 2, 'dimension' => 'intrinsic_load',
         'question' => 'La actividad cubrió conceptos que encontré muy complejos.'],
        ['id' => '3', 'item_number' => 3, 'dimension' => 'intrinsic_load',
         'question' => 'La actividad cubrió fórmulas/procedimientos que encontré muy complejos.'],

        // Carga Extrínseca (items 4-6)
        ['id' => '4', 'item_number' => 4, 'dimension' => 'extraneous_load',
         'question' => 'Las instrucciones y/o explicaciones durante la actividad fueron muy poco claras.'],
        ['id' => '5', 'item_number' => 5, 'dimension' => 'extraneous_load',
         'question' => 'Las instrucciones y/o explicaciones fueron, en términos de aprendizaje, muy ineficaces.'],
        ['id' => '6', 'item_number' => 6, 'dimension' => 'extraneous_load',
         'question' => 'Las instrucciones y/o explicaciones estaban llenas de lenguaje poco claro.'],

        // Carga Germana (items 7-10)
        ['id' => '7', 'item_number' => 7, 'dimension' => 'germane_load',
         'question' => 'La actividad realmente mejoró mi comprensión de los temas cubiertos.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'germane_load',
         'question' => 'La actividad realmente mejoró mi conocimiento y comprensión de los conceptos.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'germane_load',
         'question' => 'La actividad realmente mejoró mi comprensión de las fórmulas/procedimientos.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'germane_load',
         'question' => 'La actividad realmente mejoró mi capacidad de conectar los temas cubiertos con lo que ya sabía.'],
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
                'title' => 'Escala de Carga Cognitiva',
                'description' => 'Escala de carga cognitiva basada en Leppink et al. (2013). Mide tres tipos de carga cognitiva: intrínseca (complejidad del contenido), extrínseca (calidad de la instrucción) y germana (contribución al aprendizaje). 10 ítems en escala de 0 a 10.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Leppink, J., Paas, F., Van der Vleuten, C. P. M., Van Gog, T., & Van Merriënboer, J. J. G. (2013)',
                    'scale_type' => 'likert_11',
                    'scale_anchors' => [
                        'min' => 'Nada en absoluto (0)',
                        'max' => 'Completamente (10)',
                    ],
                    'dimensions' => [
                        'intrinsic_load' => ['items' => [1, 2, 3], 'label' => 'Carga Intrínseca'],
                        'extraneous_load' => ['items' => [4, 5, 6], 'label' => 'Carga Extrínseca'],
                        'germane_load' => ['items' => [7, 8, 9, 10], 'label' => 'Carga Germana'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 10,
            ]
        );

        $this->command->info('Plantilla de Escala de Carga Cognitiva creada (10 items)');
    }
}
