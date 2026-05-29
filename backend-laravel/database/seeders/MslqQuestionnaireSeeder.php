<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class MslqQuestionnaireSeeder extends Seeder
{
    /**
     * Opciones de escala Likert 1-7 para MSLQ
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
     * Preguntas de Motivación (Items 1-31)
     * Basado en Pintrich et al. (1991) - MSLQ Manual
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

        // Autoeficacia para el Aprendizaje y el Desempeño (items 5, 6, 12, 15, 20, 21, 29, 31)
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

    /**
     * Preguntas de Estrategias de Aprendizaje (Items 32-81)
     */
    private const STRATEGIES_QUESTIONS = [
        // Organización (items 32, 42, 49, 63)
        ['id' => '32', 'item_number' => 32, 'dimension' => 'organization',
         'question' => 'Cuando estudio las lecturas de este curso, subrayo el material para ayudarme a organizar mis pensamientos.'],
        ['id' => '42', 'item_number' => 42, 'dimension' => 'organization',
         'question' => 'Cuando estudio para este curso, reviso mis notas de clase y hago un esquema de los conceptos importantes.'],
        ['id' => '49', 'item_number' => 49, 'dimension' => 'organization',
         'question' => 'Hago diagramas, gráficos o tablas simples para ayudarme a organizar el material del curso.'],
        ['id' => '63', 'item_number' => 63, 'dimension' => 'organization',
         'question' => 'Cuando estudio para este curso, reviso las lecturas y mis notas e intento encontrar las ideas más importantes.'],

        // Autorregulación Metacognitiva (items 33, 36, 41, 44, 54, 55, 56, 57, 61, 76, 78, 79)
        ['id' => '33', 'item_number' => 33, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Durante la clase frecuentemente me pierdo información importante porque estoy pensando en otras cosas.'],
        ['id' => '36', 'item_number' => 36, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Cuando leo para este curso, me hago preguntas para ayudarme a concentrar mi lectura.'],
        ['id' => '41', 'item_number' => 41, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Cuando me confundo sobre algo que estoy leyendo para esta clase, vuelvo atrás y trato de entenderlo.'],
        ['id' => '44', 'item_number' => 44, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Si los materiales del curso son difíciles de entender, cambio la forma en que leo el material.'],
        ['id' => '54', 'item_number' => 54, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Antes de estudiar nuevo material del curso a fondo, frecuentemente lo hojeo para ver cómo está organizado.'],
        ['id' => '55', 'item_number' => 55, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Me hago preguntas para asegurarme de entender el material que he estado estudiando en esta clase.'],
        ['id' => '56', 'item_number' => 56, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Trato de cambiar la forma en que estudio para ajustarme a los requisitos del curso y al estilo de enseñanza del instructor.'],
        ['id' => '57', 'item_number' => 57, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Frecuentemente me doy cuenta de que he estado leyendo para la clase pero no sé de qué se trataba.'],
        ['id' => '61', 'item_number' => 61, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Trato de pensar en un tema y decidir qué se supone que debo aprender de él en lugar de solo leerlo cuando estudio para el curso.'],
        ['id' => '76', 'item_number' => 76, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Cuando estudio para esta clase, establezco metas para dirigir mis actividades en cada período de estudio.'],
        ['id' => '78', 'item_number' => 78, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Cuando estudio para esta clase, frecuentemente trato de explicar el material a un compañero o amigo.'],
        ['id' => '79', 'item_number' => 79, 'dimension' => 'metacognitive_self_regulation',
         'question' => 'Usualmente estudio en un lugar donde pueda concentrarme en mi trabajo del curso.'],

        // Aprendizaje entre Pares (items 34, 45, 50)
        ['id' => '34', 'item_number' => 34, 'dimension' => 'peer_learning',
         'question' => 'Cuando estudio para este curso, frecuentemente trato de explicar el material a un compañero de clase o amigo.'],
        ['id' => '45', 'item_number' => 45, 'dimension' => 'peer_learning',
         'question' => 'Trato de trabajar con otros estudiantes de esta clase para completar las tareas del curso.'],
        ['id' => '50', 'item_number' => 50, 'dimension' => 'peer_learning',
         'question' => 'Cuando estudio para este curso, frecuentemente reservo tiempo para discutir el material del curso con un grupo de estudiantes de la clase.'],

        // Administración del Tiempo y Ambiente de Estudio (items 35, 43, 52, 65, 70, 73, 77, 80)
        ['id' => '35', 'item_number' => 35, 'dimension' => 'time_management',
         'question' => 'Usualmente estudio en un lugar donde pueda concentrarme en mi trabajo del curso.'],
        ['id' => '43', 'item_number' => 43, 'dimension' => 'time_management',
         'question' => 'Hago buen uso de mi tiempo de estudio para este curso.'],
        ['id' => '52', 'item_number' => 52, 'dimension' => 'time_management',
         'question' => 'Me resulta difícil mantener un horario de estudio.'],
        ['id' => '65', 'item_number' => 65, 'dimension' => 'time_management',
         'question' => 'Tengo un lugar regular para estudiar.'],
        ['id' => '70', 'item_number' => 70, 'dimension' => 'time_management',
         'question' => 'Me aseguro de estar al día con las lecturas y tareas semanales de este curso.'],
        ['id' => '73', 'item_number' => 73, 'dimension' => 'time_management',
         'question' => 'Asisto a clase regularmente.'],
        ['id' => '77', 'item_number' => 77, 'dimension' => 'time_management',
         'question' => 'Frecuentemente me doy cuenta de que no dedico mucho tiempo a este curso debido a otras actividades.'],
        ['id' => '80', 'item_number' => 80, 'dimension' => 'time_management',
         'question' => 'Raramente encuentro tiempo para revisar mis notas o lecturas antes de un examen.'],

        // Regulación del Esfuerzo (items 37, 48, 60, 74)
        ['id' => '37', 'item_number' => 37, 'dimension' => 'effort_regulation',
         'question' => 'Frecuentemente me siento tan perezoso(a) o aburrido(a) cuando estudio para esta clase que abandono antes de terminar lo que planeaba hacer.'],
        ['id' => '48', 'item_number' => 48, 'dimension' => 'effort_regulation',
         'question' => 'Trabajo duro para tener un buen desempeño en esta clase incluso si no me gusta lo que estamos haciendo.'],
        ['id' => '60', 'item_number' => 60, 'dimension' => 'effort_regulation',
         'question' => 'Cuando el trabajo del curso es difícil, me rindo o solo estudio las partes fáciles.'],
        ['id' => '74', 'item_number' => 74, 'dimension' => 'effort_regulation',
         'question' => 'Incluso cuando los materiales del curso son aburridos y poco interesantes, logro seguir trabajando hasta terminar.'],

        // Pensamiento Crítico (items 38, 47, 51, 66, 71)
        ['id' => '38', 'item_number' => 38, 'dimension' => 'critical_thinking',
         'question' => 'Frecuentemente me encuentro cuestionando las cosas que escucho o leo en este curso para decidir si las encuentro convincentes.'],
        ['id' => '47', 'item_number' => 47, 'dimension' => 'critical_thinking',
         'question' => 'Cuando se presenta una teoría, interpretación o conclusión en clase o en las lecturas, trato de decidir si hay buena evidencia que la respalde.'],
        ['id' => '51', 'item_number' => 51, 'dimension' => 'critical_thinking',
         'question' => 'Trato el material del curso como un punto de partida y trato de desarrollar mis propias ideas sobre él.'],
        ['id' => '66', 'item_number' => 66, 'dimension' => 'critical_thinking',
         'question' => 'Trato de jugar con ideas propias relacionadas con lo que estoy aprendiendo en este curso.'],
        ['id' => '71', 'item_number' => 71, 'dimension' => 'critical_thinking',
         'question' => 'Siempre que leo o escucho una afirmación o conclusión en esta clase, pienso en posibles alternativas.'],

        // Repetición/Ensayo (items 39, 46, 59, 72)
        ['id' => '39', 'item_number' => 39, 'dimension' => 'rehearsal',
         'question' => 'Cuando estudio para esta clase, practico diciendo el material para mí mismo(a) una y otra vez.'],
        ['id' => '46', 'item_number' => 46, 'dimension' => 'rehearsal',
         'question' => 'Cuando estudio para esta clase, leo mis notas de clase y las lecturas del curso una y otra vez.'],
        ['id' => '59', 'item_number' => 59, 'dimension' => 'rehearsal',
         'question' => 'Memorizo palabras clave para recordar conceptos importantes en esta clase.'],
        ['id' => '72', 'item_number' => 72, 'dimension' => 'rehearsal',
         'question' => 'Hago listas de términos importantes para este curso y los memorizo.'],

        // Búsqueda de Ayuda (items 40, 58, 68, 75)
        ['id' => '40', 'item_number' => 40, 'dimension' => 'help_seeking',
         'question' => 'Aunque tenga problemas aprendiendo el material en esta clase, trato de hacerlo solo(a), sin la ayuda de nadie.'],
        ['id' => '58', 'item_number' => 58, 'dimension' => 'help_seeking',
         'question' => 'Pido al instructor que clarifique conceptos que no entiendo bien.'],
        ['id' => '68', 'item_number' => 68, 'dimension' => 'help_seeking',
         'question' => 'Cuando no puedo entender el material de este curso, pido ayuda a otro estudiante de la clase.'],
        ['id' => '75', 'item_number' => 75, 'dimension' => 'help_seeking',
         'question' => 'Trato de identificar estudiantes en esta clase a quienes pueda pedir ayuda si es necesario.'],

        // Elaboración (items 53, 62, 64, 67, 69, 81)
        ['id' => '53', 'item_number' => 53, 'dimension' => 'elaboration',
         'question' => 'Cuando estudio para esta clase, junto información de diferentes fuentes, como lecturas, discusiones y notas.'],
        ['id' => '62', 'item_number' => 62, 'dimension' => 'elaboration',
         'question' => 'Trato de relacionar las ideas de este curso con las de otros cursos siempre que sea posible.'],
        ['id' => '64', 'item_number' => 64, 'dimension' => 'elaboration',
         'question' => 'Cuando leo para esta clase, trato de relacionar el material con lo que ya sé.'],
        ['id' => '67', 'item_number' => 67, 'dimension' => 'elaboration',
         'question' => 'Cuando estudio para esta clase, escribo breves resúmenes de las ideas principales de las lecturas y mis notas de clase.'],
        ['id' => '69', 'item_number' => 69, 'dimension' => 'elaboration',
         'question' => 'Trato de entender el material de esta clase haciendo conexiones entre las lecturas y los conceptos de las conferencias.'],
        ['id' => '81', 'item_number' => 81, 'dimension' => 'elaboration',
         'question' => 'Trato de aplicar las ideas de las lecturas del curso a otras actividades de clase como discusiones y conferencias.'],
    ];

    public function run(): void
    {
        // Crear plantilla MSLQ Motivación
        $this->createMotivationTemplate();

        // Crear plantilla MSLQ Estrategias
        $this->createStrategiesTemplate();

        $this->command->info('Plantillas MSLQ creadas exitosamente:');
        $this->command->info('- MSLQ Motivación (31 items)');
        $this->command->info('- MSLQ Estrategias de Aprendizaje (50 items)');
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
                'title' => 'MSLQ - Escalas de Motivación',
                'description' => 'Cuestionario de Estrategias Motivadas para el Aprendizaje (MSLQ) - Sección de Motivación. Desarrollado por Pintrich et al. (1991). Incluye 31 ítems que miden: orientación a metas intrínsecas y extrínsecas, valor de la tarea, creencias de control, autoeficacia y ansiedad ante exámenes.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Pintrich, P. R., Smith, D. A., Garcia, T., & McKeachie, W. J. (1991)',
                    'scale_type' => 'likert_7',
                    'scale_anchors' => [
                        'min' => 'No me describe en absoluto',
                        'max' => 'Me describe totalmente',
                    ],
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
                'title' => 'MSLQ - Escalas de Estrategias de Aprendizaje',
                'description' => 'Cuestionario de Estrategias Motivadas para el Aprendizaje (MSLQ) - Sección de Estrategias de Aprendizaje. Desarrollado por Pintrich et al. (1991). Incluye 50 ítems que miden estrategias cognitivas (repetición, elaboración, organización, pensamiento crítico), metacognitivas (autorregulación) y de manejo de recursos (tiempo, esfuerzo, aprendizaje entre pares, búsqueda de ayuda).',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Pintrich, P. R., Smith, D. A., Garcia, T., & McKeachie, W. J. (1991)',
                    'scale_type' => 'likert_7',
                    'scale_anchors' => [
                        'min' => 'No me describe en absoluto',
                        'max' => 'Me describe totalmente',
                    ],
                    'dimensions' => [
                        'rehearsal' => ['items' => [39, 46, 59, 72], 'label' => 'Repetición/Ensayo'],
                        'elaboration' => ['items' => [53, 62, 64, 67, 69, 81], 'label' => 'Elaboración'],
                        'organization' => ['items' => [32, 42, 49, 63], 'label' => 'Organización'],
                        'critical_thinking' => ['items' => [38, 47, 51, 66, 71], 'label' => 'Pensamiento Crítico'],
                        'metacognitive_self_regulation' => ['items' => [33, 36, 41, 44, 54, 55, 56, 57, 61, 76, 78, 79], 'label' => 'Autorregulación Metacognitiva'],
                        'time_management' => ['items' => [35, 43, 52, 65, 70, 73, 77, 80], 'label' => 'Administración del Tiempo'],
                        'effort_regulation' => ['items' => [37, 48, 60, 74], 'label' => 'Regulación del Esfuerzo'],
                        'peer_learning' => ['items' => [34, 45, 50], 'label' => 'Aprendizaje entre Pares'],
                        'help_seeking' => ['items' => [40, 58, 68, 75], 'label' => 'Búsqueda de Ayuda'],
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
