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
                'title' => 'Test de Recuerdo Final (Post-test)',
                'description' => 'Plantilla para test de recuerdo final. El instructor debe personalizar las preguntas según el contenido específico de su curso. Evalúa la retención de conceptos clave al finalizar el curso. Permite comparación con el test de recuerdo inicial.',
                'questions' => [
                    [
                        'id' => '1',
                        'type' => 'multiple_choice',
                        'question' => '[Pregunta de ejemplo - Personalice] ¿Cuál es la definición correcta del concepto X?',
                        'options' => [
                            'Opción correcta (modifique según su curso)',
                            'Distractor 1 (modifique según su curso)',
                            'Distractor 2 (modifique según su curso)',
                            'Distractor 3 (modifique según su curso)',
                        ],
                        'correct_answer' => 0,
                    ],
                    [
                        'id' => '2',
                        'type' => 'multiple_choice',
                        'question' => '[Pregunta de ejemplo - Personalice] ¿Qué concepto describe mejor el proceso Y?',
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
                        'question' => '[Pregunta de ejemplo - Personalice] ¿Cuáles son los componentes principales de Z?',
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
                        'type' => 'multiple_choice',
                        'question' => '[Pregunta de ejemplo - Personalice] ¿En qué consiste el método W?',
                        'options' => [
                            'Distractor 1',
                            'Distractor 2',
                            'Distractor 3',
                            'Respuesta correcta (modifique)',
                        ],
                        'correct_answer' => 3,
                    ],
                    [
                        'id' => '5',
                        'type' => 'multiple_choice',
                        'question' => '[Pregunta de ejemplo - Personalice] ¿Cuál de las siguientes afirmaciones es correcta sobre V?',
                        'options' => [
                            'Respuesta correcta (modifique)',
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
                    'instructions' => 'El instructor debe reemplazar las preguntas de ejemplo con preguntas específicas de su curso que evalúen la retención de conceptos clave.',
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 15,
            ]
        );

        $this->command->info('Plantilla de Test de Recuerdo Final creada (5 preguntas ejemplo)');
    }
}
