<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class MslqMotivationFinalSeeder extends Seeder
{
    /**
     * Opciones de escala Likert 1-7 para MSLQ
     * Idénticas a MslqQuestionnaireSeeder
     */
    private const LIKERT_OPTIONS = [
        ['value' => 1, 'label' => 'No me describe en absoluto'],
        ['value' => 2, 'label' => 'Me describe muy poco'],
        ['value' => 3, 'label' => 'Me describe poco'],
        ['value' => 4, 'label' => 'Ni me describe ni deja de describirme'],
        ['value' => 5, 'label' => 'Me describe moderadamente'],
        ['value' => 6, 'label' => 'Me describe mucho'],
        ['value' => 7, 'label' => 'Me describe totalmente'],
    ];

    /**
     * Mismas 31 preguntas de motivación que el MSLQ inicial
     * Permite comparación pre/post
     */
    private const MOTIVATION_QUESTIONS = [
        // Orientación a Metas Intrínsecas (items 1, 16, 22, 24)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'En una clase como esta, prefiero materiales de curso que realmente me desafíen para poder aprender cosas nuevas.'],
        ['id' => '16', 'item_number' => 16, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'En una clase como esta, prefiero materiales de curso que despierten mi curiosidad, aunque sean difíciles de aprender.'],
        ['id' => '22', 'item_number' => 22, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'Lo más satisfactorio para mí en este curso es tratar de entender el contenido lo más profundamente posible.'],
        ['id' => '24', 'item_number' => 24, 'dimension' => 'intrinsic_goal_orientation',
         'question' => 'Cuando tengo la oportunidad en esta clase, elijo tareas de las que pueda aprender aunque no me garanticen una buena calificación.'],

        // Orientación a Metas Extrínsecas (items 7, 11, 13, 30)
        ['id' => '7', 'item_number' => 7, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'Obtener una buena calificación en esta clase es lo más satisfactorio para mí en este momento.'],
        ['id' => '11', 'item_number' => 11, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'Lo más importante para mí ahora mismo es mejorar mi promedio general, por lo que mi principal preocupación en esta clase es obtener una buena calificación.'],
        ['id' => '13', 'item_number' => 13, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'Si puedo, quiero obtener mejores calificaciones en esta clase que la mayoría de los otros estudiantes.'],
        ['id' => '30', 'item_number' => 30, 'dimension' => 'extrinsic_goal_orientation',
         'question' => 'Quiero tener un buen desempeño en esta clase porque es importante demostrar mi habilidad a mi familia, amigos, empleador u otros.'],

        // Valor de la Tarea (items 4, 10, 17, 23, 26, 27)
        ['id' => '4', 'item_number' => 4, 'dimension' => 'task_value',
         'question' => 'Pienso que podré usar lo que aprenda en este curso en otros cursos.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'task_value',
         'question' => 'Es importante para mí aprender el material de este curso.'],
        ['id' => '17', 'item_number' => 17, 'dimension' => 'task_value',
         'question' => 'Estoy muy interesado(a) en el contenido de este curso.'],
        ['id' => '23', 'item_number' => 23, 'dimension' => 'task_value',
         'question' => 'Pienso que el material de este curso es útil para que yo lo aprenda.'],
        ['id' => '26', 'item_number' => 26, 'dimension' => 'task_value',
         'question' => 'Me gusta el tema de este curso.'],
        ['id' => '27', 'item_number' => 27, 'dimension' => 'task_value',
         'question' => 'Entender el tema de este curso es muy importante para mí.'],

        // Creencias de Control (items 2, 9, 18, 25)
        ['id' => '2', 'item_number' => 2, 'dimension' => 'control_beliefs',
         'question' => 'Si estudio de manera apropiada, podré aprender el material de este curso.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'control_beliefs',
         'question' => 'Es mi culpa si no aprendo el material de este curso.'],
        ['id' => '18', 'item_number' => 18, 'dimension' => 'control_beliefs',
         'question' => 'Si me esfuerzo lo suficiente, entenderé el material del curso.'],
        ['id' => '25', 'item_number' => 25, 'dimension' => 'control_beliefs',
         'question' => 'Si no entiendo el material del curso, es porque no me esforcé lo suficiente.'],

        // Autoeficacia (items 5, 6, 12, 15, 20, 21, 29, 31)
        ['id' => '5', 'item_number' => 5, 'dimension' => 'self_efficacy',
         'question' => 'Creo que recibiré una excelente calificación en esta clase.'],
        ['id' => '6', 'item_number' => 6, 'dimension' => 'self_efficacy',
         'question' => 'Estoy seguro(a) de que puedo entender las lecturas más difíciles de este curso.'],
        ['id' => '12', 'item_number' => 12, 'dimension' => 'self_efficacy',
         'question' => 'Confío en que puedo aprender los conceptos básicos enseñados en este curso.'],
        ['id' => '15', 'item_number' => 15, 'dimension' => 'self_efficacy',
         'question' => 'Confío en que puedo entender el material más complejo presentado por el instructor en este curso.'],
        ['id' => '20', 'item_number' => 20, 'dimension' => 'self_efficacy',
         'question' => 'Confío en que puedo hacer un excelente trabajo en las tareas y exámenes de este curso.'],
        ['id' => '21', 'item_number' => 21, 'dimension' => 'self_efficacy',
         'question' => 'Espero tener un buen desempeño en esta clase.'],
        ['id' => '29', 'item_number' => 29, 'dimension' => 'self_efficacy',
         'question' => 'Estoy seguro(a) de que puedo dominar las habilidades que se enseñan en esta clase.'],
        ['id' => '31', 'item_number' => 31, 'dimension' => 'self_efficacy',
         'question' => 'Considerando la dificultad de este curso, el profesor y mis habilidades, creo que me irá bien en esta clase.'],

        // Ansiedad ante Exámenes (items 3, 8, 14, 19, 28)
        ['id' => '3', 'item_number' => 3, 'dimension' => 'test_anxiety',
         'question' => 'Cuando presento un examen, pienso en lo mal que lo estoy haciendo comparado con otros estudiantes.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'test_anxiety',
         'question' => 'Cuando presento un examen, pienso en las preguntas de otras partes del examen que no puedo contestar.'],
        ['id' => '14', 'item_number' => 14, 'dimension' => 'test_anxiety',
         'question' => 'Cuando presento exámenes, pienso en las consecuencias de reprobar.'],
        ['id' => '19', 'item_number' => 19, 'dimension' => 'test_anxiety',
         'question' => 'Tengo una sensación de inquietud y malestar cuando presento un examen.'],
        ['id' => '28', 'item_number' => 28, 'dimension' => 'test_anxiety',
         'question' => 'Siento que mi corazón late rápido cuando presento un examen.'],
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
                'title' => 'MSLQ - Escalas de Motivación (Post-test)',
                'description' => 'Cuestionario de Estrategias Motivadas para el Aprendizaje (MSLQ) - Sección de Motivación (Post-test). Mismos 31 ítems que la versión inicial para permitir comparación pre/post. Mide: orientación a metas intrínsecas y extrínsecas, valor de la tarea, creencias de control, autoeficacia y ansiedad ante exámenes.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Pintrich, P. R., Smith, D. A., Garcia, T., & McKeachie, W. J. (1991)',
                    'scale_type' => 'likert_7',
                    'scale_anchors' => [
                        'min' => 'No me describe en absoluto',
                        'max' => 'Me describe totalmente',
                    ],
                    'is_post_test' => true,
                    'pre_test_type' => 'mslq_motivation_initial',
                    'dimensions' => [
                        'intrinsic_goal_orientation' => ['items' => [1, 16, 22, 24], 'label' => 'Orientación a Metas Intrínsecas'],
                        'extrinsic_goal_orientation' => ['items' => [7, 11, 13, 30], 'label' => 'Orientación a Metas Extrínsecas'],
                        'task_value' => ['items' => [4, 10, 17, 23, 26, 27], 'label' => 'Valor de la Tarea'],
                        'control_beliefs' => ['items' => [2, 9, 18, 25], 'label' => 'Creencias de Control'],
                        'self_efficacy' => ['items' => [5, 6, 12, 15, 20, 21, 29, 31], 'label' => 'Autoeficacia'],
                        'test_anxiety' => ['items' => [3, 8, 14, 19, 28], 'label' => 'Ansiedad ante Exámenes'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 20,
            ]
        );

        $this->command->info('Plantilla MSLQ Motivación Final (Post-test) creada (31 items)');
    }
}
