<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * GET /assessment-templates
     * Lista todas las plantillas de evaluación disponibles
     */
    public function index(Request $request)
    {
        $templates = Assessment::templates()
            ->select([
                'id',
                'assessment_type',
                'title',
                'description',
                'config',
                'time_limit',
                'requires_manual_grading',
            ])
            ->withCount('derivedAssessments as usage_count')
            ->get();

        // Agregar conteo de preguntas
        $templates->each(function ($template) {
            $template->questions_count = count($template->questions ?? []);
        });

        // Agrupar por tipo de evaluación
        $grouped = $templates->groupBy('assessment_type')->map(function ($group) {
            return $group->values();
        });

        return response()->json([
            'templates' => $templates,
            'grouped' => $grouped,
            'count' => $templates->count(),
        ]);
    }

    /**
     * GET /assessment-templates/{template}
     * Muestra detalles de una plantilla específica
     */
    public function show(Request $request, Assessment $template)
    {
        if (!$template->is_template) {
            return response()->json([
                'message' => 'No es una plantilla válida',
            ], 404);
        }

        // Extraer información de dimensiones
        $dimensions = $this->extractDimensions($template);

        // Conteo de preguntas por tipo
        $questionTypes = collect($template->questions ?? [])
            ->groupBy('type')
            ->map(fn($group) => $group->count());

        return response()->json([
            'template' => $template,
            'questions_count' => count($template->questions ?? []),
            'dimensions' => $dimensions,
            'question_types' => $questionTypes,
            'usage_count' => $template->derivedAssessments()->count(),
        ]);
    }

    /**
     * POST /courses/{course}/assessments/from-template/{template}
     * Crea una evaluación desde una plantilla
     */
    public function createFromTemplate(Request $request, Course $course, Assessment $template)
    {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado para gestionar este curso',
            ], 403);
        }

        // Verificar que sea una plantilla válida
        if (!$template->is_template) {
            return response()->json([
                'message' => 'No es una plantilla válida',
            ], 400);
        }

        // Verificar si ya existe una evaluación de este tipo en el curso
        $existingAssessment = Assessment::where('course_id', $course->id)
            ->where('assessment_type', $template->assessment_type)
            ->first();

        if ($existingAssessment) {
            return response()->json([
                'message' => 'Ya existe una evaluación de este tipo en el curso',
                'existing_assessment' => [
                    'id' => $existingAssessment->id,
                    'title' => $existingAssessment->title,
                    'is_active' => $existingAssessment->is_active,
                ],
            ], 409);
        }

        // Validar campos opcionales de personalización
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'config' => 'nullable|array',
        ]);

        try {
            $assessment = Assessment::createFromTemplate(
                $template->id,
                $course->id,
                $validated
            );

            return response()->json([
                'message' => 'Evaluación creada exitosamente desde plantilla',
                'assessment' => $assessment,
                'source_template' => [
                    'id' => $template->id,
                    'title' => $template->title,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear evaluación desde plantilla',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /courses/{course}/available-templates
     * Lista plantillas disponibles para un curso (excluyendo las ya usadas)
     */
    public function availableForCourse(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Obtener tipos de evaluación ya existentes en el curso
        $existingTypes = Assessment::where('course_id', $course->id)
            ->pluck('assessment_type')
            ->toArray();

        // Obtener plantillas que no han sido usadas
        $availableTemplates = Assessment::templates()
            ->whereNotIn('assessment_type', $existingTypes)
            ->select(['id', 'assessment_type', 'title', 'description', 'time_limit'])
            ->get();

        // Obtener plantillas ya usadas (para referencia)
        $usedTemplates = Assessment::templates()
            ->whereIn('assessment_type', $existingTypes)
            ->select(['id', 'assessment_type', 'title'])
            ->get();

        return response()->json([
            'available_templates' => $availableTemplates,
            'used_templates' => $usedTemplates,
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
        ]);
    }

    /**
     * Extrae información de dimensiones de las preguntas de la plantilla
     */
    private function extractDimensions(Assessment $template): array
    {
        $dimensions = [];

        foreach ($template->questions ?? [] as $question) {
            if (isset($question['dimension'])) {
                $dim = $question['dimension'];
                if (!isset($dimensions[$dim])) {
                    $dimensions[$dim] = [
                        'name' => $dim,
                        'label' => $template->config['dimensions'][$dim]['label'] ?? ucwords(str_replace('_', ' ', $dim)),
                        'items' => [],
                        'count' => 0,
                    ];
                }
                $dimensions[$dim]['items'][] = $question['item_number'] ?? $question['id'];
                $dimensions[$dim]['count']++;
            }
        }

        return array_values($dimensions);
    }

    /**
     * Verifica si el usuario puede gestionar el curso
     */
    private function canManageCourse($user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isInstructor() && $course->instructor_id === $user->id) {
            return true;
        }

        return false;
    }
}
