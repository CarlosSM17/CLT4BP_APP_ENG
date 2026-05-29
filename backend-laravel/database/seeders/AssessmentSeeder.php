<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el primer curso para ejemplos
        $course = Course::first();

        if (!$course) {
            $this->command->info('No hay cursos disponibles. Ejecuta CourseSeeder primero.');
            return;
        }

        // Evaluación Recall Inicial
        Assessment::create([
            'course_id' => $course->id,
            'assessment_type' => 'recall_initial',
            'title' => 'Evaluación de Conocimientos Previos',
            'description' => 'Evalúa tus conocimientos antes de comenzar el curso',
            'questions' => [
                [
                    'id' => '1',
                    'type' => 'multiple_choice',
                    'question' => '¿Qué es un algoritmo?',
                    'options' => [
                        'Una secuencia de instrucciones para resolver un problema',
                        'Un tipo de dato en programación',
                        'Un lenguaje de programación',
                        'Una herramienta de depuración'
                    ],
                    'correct_answer' => 0
                ],
                [
                    'id' => '2',
                    'type' => 'multiple_choice',
                    'question' => '¿Cuál de estos NO es un tipo de dato primitivo?',
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
                    'question' => 'Describe con tus propias palabras qué es una variable en programación.',
                    'options' => []
                ]
            ],
            'is_active' => true,
            'time_limit' => 15,
        ]);

        // Evaluación MSLQ Motivación Inicial
        Assessment::create([
            'course_id' => $course->id,
            'assessment_type' => 'mslq_motivation_initial',
            'title' => 'Cuestionario de Motivación MSLQ',
            'description' => 'Evalúa tu motivación y actitud hacia el aprendizaje',
            'questions' => [
                [
                    'id' => '1',
                    'type' => 'likert',
                    'question' => 'Estoy muy interesado en el contenido de este curso.',
                    'options' => []
                ],
                [
                    'id' => '2',
                    'type' => 'likert',
                    'question' => 'Creo que puedo entender incluso los conceptos más difíciles presentados en este curso.',
                    'options' => []
                ],
                [
                    'id' => '3',
                    'type' => 'likert',
                    'question' => 'Espero tener un buen desempeño en este curso.',
                    'options' => []
                ],
                [
                    'id' => '4',
                    'type' => 'likert',
                    'question' => 'Pienso que este curso es útil para mi aprendizaje.',
                    'options' => []
                ],
                [
                    'id' => '5',
                    'type' => 'likert',
                    'question' => 'Me gusta el tema que se aborda en este curso.',
                    'options' => []
                ]
            ],
            'is_active' => true,
            'time_limit' => 10,
        ]);

        // Evaluación de Carga Cognitiva
        Assessment::create([
            'course_id' => $course->id,
            'assessment_type' => 'cognitive_load',
            'title' => 'Evaluación de Carga Cognitiva',
            'description' => 'Evalúa el esfuerzo mental durante el aprendizaje',
            'questions' => [
                [
                    'id' => '1',
                    'type' => 'scale',
                    'question' => '¿Qué tan difícil te resultó el contenido de esta lección? (1=Muy fácil, 5=Muy difícil)',
                    'options' => []
                ],
                [
                    'id' => '2',
                    'type' => 'scale',
                    'question' => '¿Cuánto esfuerzo mental invertiste? (1=Muy poco, 5=Mucho)',
                    'options' => []
                ],
                [
                    'id' => '3',
                    'type' => 'scale',
                    'question' => '¿Qué tan confuso te pareció el material? (1=Nada confuso, 5=Muy confuso)',
                    'options' => []
                ],
                [
                    'id' => '4',
                    'type' => 'text',
                    'question' => '¿Qué parte del contenido te resultó más desafiante?',
                    'options' => []
                ]
            ],
            'is_active' => false,
            'time_limit' => 5,
        ]);

        $this->command->info('Evaluaciones de ejemplo creadas exitosamente.');
    }
}
