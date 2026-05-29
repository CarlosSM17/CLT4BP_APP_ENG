<?php

namespace Database\Seeders;

use App\Models\Assessment;
use Illuminate\Database\Seeder;

class ImmsSeeder extends Seeder
{
    /**
     * Opciones de escala Likert 1-5 para IMMS
     */
    private const LIKERT_OPTIONS = [
        ['value' => 1, 'label' => 'Totalmente en desacuerdo'],
        ['value' => 2, 'label' => 'En desacuerdo'],
        ['value' => 3, 'label' => 'Ni de acuerdo ni en desacuerdo'],
        ['value' => 4, 'label' => 'De acuerdo'],
        ['value' => 5, 'label' => 'Totalmente de acuerdo'],
    ];

    /**
     * Items del IMMS - 36 items
     * Basado en Keller, J. M. (2010). Motivational Design for Learning and Performance.
     * Dimensiones ARCS: Attention (12), Relevance (9), Confidence (9), Satisfaction (6)
     */
    private const QUESTIONS = [
        // Atención (12 items: 2, 8, 11, 12, 15, 17, 20, 22, 24, 28, 29, 31)
        ['id' => '2', 'item_number' => 2, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'Había algo interesante al inicio del material instruccional que captó mi atención.'],
        ['id' => '8', 'item_number' => 8, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'El material instruccional fue visualmente atractivo.'],
        ['id' => '11', 'item_number' => 11, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'La calidad de la redacción ayudó a mantener mi atención.'],
        ['id' => '12', 'item_number' => 12, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'El material instruccional es tan abstracto que fue difícil mantener mi atención en él.'],
        ['id' => '15', 'item_number' => 15, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'El diseño del material instruccional parece aburrido y poco atractivo.'],
        ['id' => '17', 'item_number' => 17, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'La forma en que la información está organizada en las páginas ayudó a mantener mi atención.'],
        ['id' => '20', 'item_number' => 20, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'El material instruccional tiene cosas que estimularon mi curiosidad.'],
        ['id' => '22', 'item_number' => 22, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'La cantidad de repetición en el material hizo que a veces me aburriera.'],
        ['id' => '24', 'item_number' => 24, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'Aprendí algunas cosas que fueron sorprendentes o inesperadas.'],
        ['id' => '28', 'item_number' => 28, 'dimension' => 'attention', 'reverse' => false,
         'question' => 'La variedad de pasajes de lectura, ejercicios, ilustraciones, etc., ayudó a mantener mi atención en el material.'],
        ['id' => '29', 'item_number' => 29, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'El estilo de escritura es aburrido.'],
        ['id' => '31', 'item_number' => 31, 'dimension' => 'attention', 'reverse' => true,
         'question' => 'Hay tanta información que es difícil identificar y recordar los puntos importantes.'],

        // Relevancia (9 items: 6, 9, 10, 16, 18, 23, 26, 30, 33)
        ['id' => '6', 'item_number' => 6, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Está claro para mí cómo el contenido de este material está relacionado con cosas que ya conozco.'],
        ['id' => '9', 'item_number' => 9, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Había historias, ejemplos o preguntas que me mostraron cómo este material podría ser importante para algunas personas.'],
        ['id' => '10', 'item_number' => 10, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Completar satisfactoriamente este material fue importante para mí.'],
        ['id' => '16', 'item_number' => 16, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El contenido de este material es relevante para mis intereses.'],
        ['id' => '18', 'item_number' => 18, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Hay explicaciones o ejemplos de cómo las personas usan el conocimiento de este material.'],
        ['id' => '23', 'item_number' => 23, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El contenido y el estilo de escritura de este material dan la impresión de que vale la pena conocerlo.'],
        ['id' => '26', 'item_number' => 26, 'dimension' => 'relevance', 'reverse' => true,
         'question' => 'Este material no fue relevante para mis necesidades porque ya conocía la mayor parte.'],
        ['id' => '30', 'item_number' => 30, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'Pude relacionar el contenido de este material con cosas que he visto, hecho o pensado en mi vida cotidiana.'],
        ['id' => '33', 'item_number' => 33, 'dimension' => 'relevance', 'reverse' => false,
         'question' => 'El contenido de este material será útil para mí.'],

        // Confianza (9 items: 1, 3, 4, 7, 13, 19, 25, 34, 35)
        ['id' => '1', 'item_number' => 1, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Cuando vi por primera vez el material instruccional, tuve la impresión de que sería fácil para mí.'],
        ['id' => '3', 'item_number' => 3, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Este material fue más difícil de entender de lo que me habría gustado.'],
        ['id' => '4', 'item_number' => 4, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Después de leer la información introductoria, me sentí confiado(a) de saber qué debía aprender de este material.'],
        ['id' => '7', 'item_number' => 7, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Muchas de las páginas tenían tanta información que fue difícil identificar y recordar los puntos importantes.'],
        ['id' => '13', 'item_number' => 13, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Mientras trabajaba con este material, estaba confiado(a) de que podía aprender el contenido.'],
        ['id' => '19', 'item_number' => 19, 'dimension' => 'confidence', 'reverse' => true,
         'question' => 'Los ejercicios en este material fueron demasiado difíciles.'],
        ['id' => '25', 'item_number' => 25, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Después de trabajar con este material por un tiempo, estaba confiado(a) de que pasaría una evaluación sobre él.'],
        ['id' => '34', 'item_number' => 34, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'Pude entender bastante bien el material de estudio.'],
        ['id' => '35', 'item_number' => 35, 'dimension' => 'confidence', 'reverse' => false,
         'question' => 'La buena organización del contenido me ayudó a sentir confianza de que aprendería este material.'],

        // Satisfacción (6 items: 5, 14, 21, 27, 32, 36)
        ['id' => '5', 'item_number' => 5, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Completar los ejercicios de este material me dio un sentimiento satisfactorio de logro.'],
        ['id' => '14', 'item_number' => 14, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Disfruté este material tanto que me gustaría saber más sobre este tema.'],
        ['id' => '21', 'item_number' => 21, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Disfruté estudiando este material.'],
        ['id' => '27', 'item_number' => 27, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'La retroalimentación después de los ejercicios, o los comentarios en el material, me ayudaron a sentirme recompensado(a) por mi esfuerzo.'],
        ['id' => '32', 'item_number' => 32, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Me sentí bien al completar satisfactoriamente este material.'],
        ['id' => '36', 'item_number' => 36, 'dimension' => 'satisfaction', 'reverse' => false,
         'question' => 'Fue un placer trabajar con un material instruccional tan bien diseñado.'],
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
                'title' => 'Encuesta de Motivación de Materiales Instruccionales (IMMS)',
                'description' => 'Encuesta de Motivación de Materiales Instruccionales basada en el modelo ARCS de Keller (2010). Evalúa la motivación generada por los materiales instruccionales en cuatro dimensiones: Atención, Relevancia, Confianza y Satisfacción. 36 ítems en escala Likert de 1 a 5.',
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
                        'attention' => ['items' => [2, 8, 11, 12, 15, 17, 20, 22, 24, 28, 29, 31], 'label' => 'Atención'],
                        'relevance' => ['items' => [6, 9, 10, 16, 18, 23, 26, 30, 33], 'label' => 'Relevancia'],
                        'confidence' => ['items' => [1, 3, 4, 7, 13, 19, 25, 34, 35], 'label' => 'Confianza'],
                        'satisfaction' => ['items' => [5, 14, 21, 27, 32, 36], 'label' => 'Satisfacción'],
                    ],
                ],
                'is_active' => false,
                'is_template' => true,
                'requires_manual_grading' => false,
                'time_limit' => 15,
            ]
        );

        $this->command->info('Plantilla IMMS (Instructional Materials Motivation Survey) creada (36 items)');
    }
}
