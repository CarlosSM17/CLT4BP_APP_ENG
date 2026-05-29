<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class CourseInterestSurveySeeder extends Seeder
{
    /**
     * Opciones de escala Likert 1-5 para CIS
     * Basado en Keller (2010) - Course Interest Survey
     */
    private const LIKERT_OPTIONS = [
        ['value' => 1, 'label' => 'Totalmente en desacuerdo'],
        ['value' => 2, 'label' => 'En desacuerdo'],
        ['value' => 3, 'label' => 'Ni de acuerdo ni en desacuerdo'],
        ['value' => 4, 'label' => 'De acuerdo'],
        ['value' => 5, 'label' => 'Totalmente de acuerdo'],
    ];

    /**
     * Items del CIS - 34 items
     * Basado en Keller, J. M. (2010). Motivational Design for Learning and Performance.
     * Dimensiones ARCS: Attention, Relevance, Confidence, Satisfaction
     */
    private const QUESTIONS = [
        // Atención (8 items: 1, 4, 10, 15, 21, 24, 26, 29)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'El instructor sabe cómo hacer que nos interesemos en el contenido del curso.'],
        ['id' => '4', 'item_number' => 4, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'Las clases de este curso son aburridas.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'El instructor utiliza una variedad interesante de técnicas de enseñanza.'],
        ['id' => '15', 'item_number' => 15, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'Los materiales de instrucción de este curso son aburridos.'],
        ['id' => '21', 'item_number' => 21, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'La variedad de tareas, ejercicios, ejemplos, etc., ayudó a mantener mi atención en el curso.'],
        ['id' => '24', 'item_number' => 24, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'Mi curiosidad fue frecuentemente estimulada por las preguntas planteadas y los problemas dados en este curso.'],
        ['id' => '26', 'item_number' => 26, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'A menudo soñaba despierto(a) durante las clases de este curso.'],
        ['id' => '29', 'item_number' => 29, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'La forma en que se presenta la información me ayudó a mantener la atención.'],

        // Relevancia (9 items: 2, 5, 8, 13, 20, 22, 23, 25, 28)
        ['id' => '2', 'item_number' => 2, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Las cosas que estoy aprendiendo en este curso serán útiles para mí.'],
        ['id' => '5', 'item_number' => 5, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El instructor hace que el contenido del curso parezca importante.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'relevance', 'reverse' => true,
         'question' => 'No puedo ver cómo el contenido de este curso se relaciona con algo que ya conozco.'],
        ['id' => '13', 'item_number' => 13, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'En este curso, trato de establecer conexiones entre el contenido y mis metas personales.'],
        ['id' => '20', 'item_number' => 20, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El contenido de este curso se relaciona con mis expectativas y metas.'],
        ['id' => '22', 'item_number' => 22, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El contenido de este curso será útil para mí.'],
        ['id' => '23', 'item_number' => 23, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Puedo relacionar el contenido de este curso con cosas que he visto, hecho o pensado en mi vida cotidiana.'],
        ['id' => '25', 'item_number' => 25, 'dimension' => 'relevance', 'reverse' => true,
         'question' => 'El contenido de este curso no será útil para mí.'],
        ['id' => '28', 'item_number' => 28, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El valor personal de este contenido hace que quiera seguir aprendiendo sobre él.'],

        // Confianza (8 items: 3, 6, 9, 11, 17, 27, 30, 34)
        ['id' => '3', 'item_number' => 3, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Siento confianza en que me irá bien en este curso.'],
        ['id' => '6', 'item_number' => 6, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Es difícil predecir qué calificación me dará el instructor en este curso.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Tanto si estudio mucho como si estudio poco para este curso, no me hace ninguna diferencia.'],
        ['id' => '11', 'item_number' => 11, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Puedo entender bien los materiales de este curso.'],
        ['id' => '17', 'item_number' => 17, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Fue fácil para mí entender las tareas de este curso.'],
        ['id' => '27', 'item_number' => 27, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'No puedo entender realmente buena parte del material de este curso.'],
        ['id' => '30', 'item_number' => 30, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'La cantidad de trabajo que tengo que hacer es apropiada para este tipo de curso.'],
        ['id' => '34', 'item_number' => 34, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Después de trabajar en las actividades, sentí confianza en que podía pasar las evaluaciones sobre el contenido.'],

        // Satisfacción (9 items: 7, 12, 14, 16, 18, 19, 31, 32, 33)
        ['id' => '7', 'item_number' => 7, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'El instructor hace que la materia de este curso sea agradable.'],
        ['id' => '12', 'item_number' => 12, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Me sentí bien al completar exitosamente este curso.'],
        ['id' => '14', 'item_number' => 14, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Me disfruté tanto este curso que me gustaría saber más sobre este tema.'],
        ['id' => '16', 'item_number' => 16, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Disfruté este curso tanto que desearía que más cursos fueran conducidos de la misma manera.'],
        ['id' => '18', 'item_number' => 18, 'dimension' => 'satisfaction', 'reverse' => true,
         'question' => 'No me gustó este curso y no quisiera tomarlo de nuevo.'],
        ['id' => '19', 'item_number' => 19, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Sentí satisfacción cuando sabía que estaba aprendiendo.'],
        ['id' => '31', 'item_number' => 31, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Me sentí bien al completar las tareas de este curso.'],
        ['id' => '32', 'item_number' => 32, 'dimension' => 'satisfaction', 'reverse' => true,
         'question' => 'Fue un desperdicio de tiempo tomar este curso.'],
        ['id' => '33', 'item_number' => 33, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'El instructor se mostró interesado en que los estudiantes aprendieran en este curso.'],
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
                'title' => 'Encuesta de Interés en el Curso (CIS)',
                'description' => 'Encuesta de Interés en el Curso basada en el modelo ARCS de Keller (2010). Mide cuatro dimensiones motivacionales: Atención, Relevancia, Confianza y Satisfacción. 34 ítems en escala Likert de 1 a 5.',
                'questions' => $questions,
                'config' => [
                    'version' => '1.0',
                    'source' => 'Keller, J. M. (2010). Motivational Design for Learning and Performance: The ARCS Model Approach.',
                    'scale_type' => 'likert_5',
                    'scale_anchors' => [
                        'min' => 'Totalmente en desacuerdo (1)',
                        'max' => 'Totalmente de acuerdo (5)',
                    ],
                    'reverse_items' => $reverseItems,
                    'dimensions' => [
                        'attention' => ['items' => [1, 4, 10, 15, 21, 24, 26, 29], 'label' => 'Atención'],
                        'relevance' => ['items' => [2, 5, 8, 13, 20, 22, 23, 25, 28], 'label' => 'Relevancia'],
                        'confidence' => ['items' => [3, 6, 9, 11, 17, 27, 30, 34], 'label' => 'Confianza'],
                        'satisfaction' => ['items' => [7, 12, 14, 16, 18, 19, 31, 32, 33], 'label' => 'Satisfacción'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 15,
            ]
        );

        $this->command->info('Plantilla CIS (Course Interest Survey) creada (34 items)');
    }
}
