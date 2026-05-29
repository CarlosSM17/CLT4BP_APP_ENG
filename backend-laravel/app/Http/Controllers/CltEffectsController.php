<?php

namespace App\Http\Controllers;

use App\Models\CltEffectsSelection;
use App\Models\Course;
use App\Services\AiServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CltEffectsController extends Controller
{
    private AiServiceClient $aiService;

    // Definición estática de efectos CLT (fallback si Python no está disponible)
    private const CLT_EFFECTS = [
        // Efectos para Nuevo Conocimiento
        [
            'id' => 'goal_free',
            'name' => 'Solución Libre (Goal-Free Effect)',
            'description' => 'Eliminar metas específicas para reducir carga cognitiva y permitir exploración',
            'category' => 'new_knowledge',
            'application_guidance' => 'Presenta problemas sin objetivos específicos, permitiendo que el estudiante explore diferentes soluciones'
        ],
        [
            'id' => 'worked_example',
            'name' => 'Ejemplo Resuelto (Worked Example Effect)',
            'description' => 'Mostrar ejemplos completamente resueltos con explicaciones paso a paso',
            'category' => 'new_knowledge',
            'application_guidance' => 'Incluye ejemplos completamente trabajados antes de tareas similares'
        ],
        [
            'id' => 'completion_problem',
            'name' => 'Problema por Completar (Completion Problem)',
            'description' => 'Proporcionar problemas parcialmente resueltos para completar',
            'category' => 'new_knowledge',
            'application_guidance' => 'Da problemas iniciados que el estudiante debe completar'
        ],
        [
            'id' => 'split_attention',
            'name' => 'Atención Dividida (Split Attention)',
            'description' => 'Integrar información relacionada espacialmente para evitar división de atención',
            'category' => 'new_knowledge',
            'application_guidance' => 'Mantén texto explicativo cerca de diagramas/código relacionado'
        ],
        [
            'id' => 'modality',
            'name' => 'Modalidad (Modality Effect)',
            'description' => 'Usar combinación de modos de presentación (visual + auditivo)',
            'category' => 'new_knowledge',
            'application_guidance' => 'Combina explicaciones textuales con descripciones verbales cuando sea posible'
        ],
        [
            'id' => 'redundancy',
            'name' => 'Redundancia (Redundancy Effect)',
            'description' => 'Evitar información redundante que no agrega valor',
            'category' => 'new_knowledge',
            'application_guidance' => 'Elimina información duplicada; presenta cada concepto una vez de forma clara'
        ],
        [
            'id' => 'variability',
            'name' => 'Variabilidad (Variability Effect)',
            'description' => 'Usar múltiples ejemplos variados del mismo concepto',
            'category' => 'new_knowledge',
            'application_guidance' => 'Proporciona varios ejemplos que muestren diferentes aplicaciones del mismo concepto'
        ],
        [
            'id' => 'isolated_elements',
            'name' => 'Elementos Aislados (Isolated Elements)',
            'description' => 'Presentar elementos complejos de forma aislada primero',
            'category' => 'new_knowledge',
            'application_guidance' => 'Introduce conceptos complejos elemento por elemento antes de combinarlos'
        ],
        [
            'id' => 'element_interactivity',
            'name' => 'Interactividad (Element Interactivity)',
            'description' => 'Gestionar la interactividad entre elementos de información',
            'category' => 'new_knowledge',
            'application_guidance' => 'Estructura el contenido para minimizar elementos que deben procesarse simultáneamente'
        ],
        // Efectos para Conocimiento Previo
        [
            'id' => 'self_explanation',
            'name' => 'Auto-explicación (Self-Explanation)',
            'description' => 'Promover que el estudiante explique conceptos con sus propias palabras',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Incluye preguntas que requieran que el estudiante explique el por qué y cómo'
        ],
        [
            'id' => 'imagination',
            'name' => 'Imaginación (Imagination Effect)',
            'description' => 'Pedir al estudiante que imagine o visualice procedimientos',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Solicita que el estudiante visualice mentalmente procesos antes de ejecutarlos'
        ],
        [
            'id' => 'expertise_reversal',
            'name' => 'Reversión de Experiencia (Expertise Reversal)',
            'description' => 'Reducir guía explícita para estudiantes con conocimiento previo',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Minimiza instrucciones paso a paso; proporciona problemas más abiertos'
        ],
        [
            'id' => 'guidance_fading',
            'name' => 'Desvanecimiento de Guía (Guidance-Fading)',
            'description' => 'Reducir gradualmente el nivel de guía proporcionada',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Comienza con guía completa y reduce progresivamente el soporte'
        ],
        [
            'id' => 'collective_memory',
            'name' => 'Memoria Colectiva (Collective Memory)',
            'description' => 'Aprovechar conocimiento compartido en actividades grupales',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Diseña actividades que permitan a estudiantes compartir conocimientos previos'
        ],
        [
            'id' => 'self_management',
            'name' => 'Autogestión (Self-Management)',
            'description' => 'Fomentar que estudiantes gestionen su propio aprendizaje',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Da autonomía al estudiante en la selección de estrategias y secuencia de aprendizaje'
        ],
        [
            'id' => 'human_movement',
            'name' => 'Movimiento Humano (Human Movement)',
            'description' => 'Incorporar actividades que involucren movimiento físico',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Sugiere actividades prácticas que requieran movimiento o manipulación física'
        ],
        [
            'id' => 'transient_information',
            'name' => 'Información Transitoria (Transient Information)',
            'description' => 'Gestionar información temporal para evitar sobrecarga',
            'category' => 'prior_knowledge',
            'application_guidance' => 'Proporciona referencias permanentes para información que desaparece rápidamente'
        ],
    ];

    public function __construct(AiServiceClient $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Verificar si el usuario puede gestionar el curso
     */
    private function canManageCourse(int $courseId): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if ($user->role === 'admin') return true;

        if ($user->role === 'instructor') {
            $course = Course::find($courseId);
            return $course && $course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Obtener todos los efectos CLT disponibles
     */
    public function getAvailableEffects()
    {
        // Intentar obtener del servicio Python, si falla usar fallback local
        try {
            $effects = $this->aiService->getCltEffects();
            return response()->json([
                'success' => true,
                'data' => $effects
            ]);
        } catch (\Exception $e) {
            // Fallback: retornar efectos definidos localmente
            return response()->json([
                'success' => true,
                'data' => [
                    'effects' => self::CLT_EFFECTS,
                    'categories' => [
                        'new_knowledge' => 'Efectos para Nuevo Conocimiento',
                        'prior_knowledge' => 'Efectos para Conocimiento Previo'
                    ]
                ]
            ]);
        }
    }

    /**
     * Guardar selección de efectos CLT para un curso
     */
    public function saveSelection(Request $request, int $courseId)
    {
        if (!$this->canManageCourse($courseId)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'selected_effects' => 'required|array|min:1',
            'selected_effects.*' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $selection = CltEffectsSelection::updateOrCreate(
            ['course_id' => $courseId],
            [
                'selected_effects' => $validated['selected_effects'],
                'notes' => $validated['notes'] ?? null
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Selección guardada exitosamente',
            'data' => $selection
        ]);
    }

    /**
     * Obtener selección de efectos CLT de un curso
     */
    public function getSelection(int $courseId)
    {
        if (!$this->canManageCourse($courseId)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $selection = CltEffectsSelection::where('course_id', $courseId)->first();

        if (!$selection) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No hay selección guardada'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $selection
        ]);
    }

    /**
     * Obtener recomendaciones de efectos CLT basadas en el perfil grupal
     */
    public function getRecommendations(int $courseId)
    {
        $this->authorize('manage', Course::findOrFail($courseId));

        try {
            $groupProfile = \App\Models\GroupProfile::where('course_id', $courseId)->first();

            if (!$groupProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Genera primero el perfil grupal'
                ], 400);
            }

            $recommendations = $this->generateRecommendations($groupProfile);

            return response()->json([
                'success' => true,
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar recomendaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateRecommendations($groupProfile): array
    {
        $profileData = $groupProfile->profile_data;
        $knowledgeLevel = $profileData['group_summary']['predominant_knowledge'];

        $recommendations = [];

        if ($knowledgeLevel === 'bajo') {
            $recommendations = [
                'worked_example',
                'completion_problem',
                'split_attention',
                'redundancy',
                'isolated_elements'
            ];
        } elseif ($knowledgeLevel === 'alto') {
            $recommendations = [
                'self_explanation',
                'imagination',
                'expertise_reversal',
                'guidance_fading'
            ];
        } else {
            $recommendations = [
                'worked_example',
                'variability',
                'self_explanation',
                'guidance_fading'
            ];
        }

        return [
            'recommended_effects' => $recommendations,
            'reasoning' => $this->getRecommendationReasoning($knowledgeLevel)
        ];
    }

    private function getRecommendationReasoning(string $knowledgeLevel): string
    {
        $reasons = [
            'bajo' => 'El grupo tiene conocimiento previo limitado. Se recomiendan efectos CLT que reduzcan la carga cognitiva intrínseca y proporcionen soporte estructurado.',
            'medio' => 'El grupo tiene conocimiento previo moderado. Se recomienda una combinación de efectos que proporcionen soporte pero también promuevan el pensamiento activo.',
            'alto' => 'El grupo tiene buen conocimiento previo. Se recomiendan efectos que promuevan el procesamiento profundo y reduzcan guía explícita que podría resultar redundante.'
        ];

        return $reasons[$knowledgeLevel] ?? '';
    }
}
