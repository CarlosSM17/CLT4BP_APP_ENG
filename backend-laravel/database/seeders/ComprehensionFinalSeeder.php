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
                'title' => 'Test de Comprensión Final (Post-test)',
                'description' => 'Plantilla para test de comprensión final. El instructor debe personalizar las preguntas según el contenido de su curso. Evalúa la comprensión profunda (aplicación, análisis) al finalizar el curso. Incluye preguntas de opción múltiple y preguntas abiertas. Permite comparación con el test de comprensión inicial.',
                'questions' => [
                    [
                        'id' => '1',
                        'type' => 'multiple_choice',
                        'question' => '[Personalice] Dado el siguiente escenario, ¿cuál sería la mejor solución aplicando el concepto X?',
                        'options' => [
                            'Respuesta correcta (modifique según su curso)',
                            'Distractor plausible 1',
                            'Distractor plausible 2',
                            'Distractor plausible 3',
                        ],
                        'correct_answer' => 0,
                    ],
                    [
                        'id' => '2',
                        'type' => 'multiple_choice',
                        'question' => '[Personalice] ¿Qué resultado se obtendría al aplicar el procedimiento Y en la situación descrita?',
                        'options' => [
                            'Distractor 1',
                            'Respuesta correcta (modifique)',
                            'Distractor 2',
                            'Distractor 3',
                        ],
                        'correct_answer' => 1,
                    ],
                    [
                        'id' => '3',
                        'type' => 'multiple_choice',
                        'question' => '[Personalice] ¿Cuál es la principal diferencia entre el enfoque A y el enfoque B para resolver el problema Z?',
                        'options' => [
                            'Distractor 1',
                            'Distractor 2',
                            'Respuesta correcta (modifique)',
                            'Distractor 3',
                        ],
                        'correct_answer' => 2,
                    ],
                    [
                        'id' => '4',
                        'type' => 'text',
                        'question' => '[Personalice] Explique con sus propias palabras cómo se aplica el concepto X para resolver un problema del tipo Y. Proporcione un ejemplo concreto.',
                        'options' => [],
                    ],
                    [
                        'id' => '5',
                        'type' => 'text',
                        'question' => '[Personalice] Analice las ventajas y desventajas de utilizar el método W en comparación con el método V. ¿En qué situaciones recomendaría cada uno?',
                        'options' => [],
                    ],
                ],
                'config' => [
                    'version' => '1.0',
                    'is_post_test' => true,
                    'pre_test_type' => 'comprehension_initial',
                    'instructions' => 'El instructor debe reemplazar las preguntas de ejemplo con preguntas de comprensión específicas de su curso. Se recomienda mantener una mezcla de preguntas de opción múltiple (aplicación) y preguntas abiertas (análisis y síntesis).',
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => true,
                'time_limit' => 20,
            ]
        );

        $this->command->info('Plantilla de Test de Comprensión Final creada (5 preguntas ejemplo)');
    }
}
