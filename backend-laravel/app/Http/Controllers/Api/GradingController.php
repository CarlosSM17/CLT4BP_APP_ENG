<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\StudentResponse;
use App\Services\GradingService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class GradingController extends Controller
{
    private GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * GET /courses/{course}/pending-grading
     * Resumen de evaluaciones pendientes de calificación en un curso
     */
    public function coursePendingGrading(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        $summary = $this->gradingService->getCoursePendingGradingSummary($course);

        return response()->json([
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
            'grading_summary' => $summary,
        ]);
    }

    /**
     * GET /courses/{course}/assessments/{assessment}/pending-grading
     * Lista respuestas pendientes de calificación para una evaluación
     */
    public function pendingGrading(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar que la evaluación pertenece al curso
        if ($assessment->course_id !== $course->id) {
            return response()->json([
                'message' => 'Evaluación no encontrada en este curso',
            ], 404);
        }

        $responses = $this->gradingService->getPendingGradingResponses($assessment);

        // Obtener preguntas abiertas para referencia
        $openEndedQuestions = collect($assessment->questions)
            ->filter(fn($q) => in_array($q['type'] ?? '', ['text', 'essay', 'open_ended']))
            ->values();

        // Obtener estadísticas de calificación
        $stats = $this->gradingService->getGradingStats($assessment);

        return response()->json([
            'assessment' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'assessment_type' => $assessment->assessment_type,
            ],
            'open_ended_questions' => $openEndedQuestions,
            'pending_responses' => $responses,
            'count' => $responses->count(),
            'stats' => $stats,
        ]);
    }

    /**
     * GET /courses/{course}/assessments/{assessment}/responses/{response}
     * Obtiene una respuesta específica para calificar
     */
    public function showResponse(
        Request $request,
        Course $course,
        Assessment $assessment,
        StudentResponse $response
    ) {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar relaciones
        if ($assessment->course_id !== $course->id) {
            return response()->json([
                'message' => 'Evaluación no encontrada en este curso',
            ], 404);
        }

        if ($response->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Respuesta no encontrada para esta evaluación',
            ], 404);
        }

        // Cargar relaciones
        $response->load(['student:id,name,email', 'grader:id,name', 'assessment']);

        // Obtener preguntas abiertas con las respuestas del estudiante
        $openEndedQuestions = collect($assessment->questions)
            ->filter(fn($q) => in_array($q['type'] ?? '', ['text', 'essay', 'open_ended']))
            ->map(function ($question) use ($response) {
                return [
                    'question' => $question,
                    'student_answer' => $response->responses[$question['id']] ?? null,
                    'manual_score' => collect($response->manual_scores ?? [])
                        ->firstWhere('question_id', $question['id']),
                ];
            })
            ->values();

        return response()->json([
            'response' => $response,
            'open_ended_details' => $openEndedQuestions,
        ]);
    }

    /**
     * POST /courses/{course}/assessments/{assessment}/responses/{response}/grade
     * Envía calificaciones manuales para una respuesta
     */
    public function submitGrade(
        Request $request,
        Course $course,
        Assessment $assessment,
        StudentResponse $response
    ) {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar relaciones
        if ($assessment->course_id !== $course->id) {
            return response()->json([
                'message' => 'Evaluación no encontrada en este curso',
            ], 404);
        }

        if ($response->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Respuesta no encontrada para esta evaluación',
            ], 404);
        }

        // Validar entrada
        $validated = $request->validate([
            'grades' => 'required|array|min:1',
            'grades.*.question_id' => 'required|string',
            'grades.*.score' => 'required|numeric|min:0',
            'grades.*.max_score' => 'required|numeric|min:1',
            'grades.*.feedback' => 'nullable|string|max:1000',
        ]);

        try {
            $gradedResponse = $this->gradingService->submitGrades(
                $response,
                $validated['grades'],
                $request->user()->id
            );

            return response()->json([
                'message' => 'Calificación guardada exitosamente',
                'response' => $gradedResponse,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /courses/{course}/assessments/{assessment}/responses/{response}/revert-grade
     * Revierte la calificación manual de una respuesta
     */
    public function revertGrade(
        Request $request,
        Course $course,
        Assessment $assessment,
        StudentResponse $response
    ) {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar relaciones
        if ($assessment->course_id !== $course->id || $response->assessment_id !== $assessment->id) {
            return response()->json([
                'message' => 'Recurso no encontrado',
            ], 404);
        }

        try {
            $revertedResponse = $this->gradingService->revertGrading($response);

            return response()->json([
                'message' => 'Calificación revertida exitosamente',
                'response' => $revertedResponse,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /courses/{course}/recalculate-grading
     * Recalcula el estado de calificación de todas las respuestas del curso
     */
    public function recalculateGradingStatus(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        $result = $this->gradingService->recalculateGradingStatusForCourse($course);

        return response()->json([
            'message' => $result['message'],
            'updated_count' => $result['updated_count'],
        ]);
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
