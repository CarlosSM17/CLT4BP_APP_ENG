<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\StudentResponse;
use App\Services\GradingService;
use Illuminate\Http\Request;

class StudentResponseController extends Controller
{
    /**
     * Iniciar evaluación (crear registro de inicio)
     */
    public function start(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar que es estudiante
        if (!$request->user()->isStudent()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verificar que está inscrito en el curso
        $isEnrolled = $course->students()->where('student_id', $request->user()->id)->exists();
        if (!$isEnrolled) {
            return response()->json(['message' => 'No estás inscrito en este curso'], 403);
        }

        // Verificar que la evaluación está activa
        if (!$assessment->is_active) {
            return response()->json(['message' => 'Esta evaluación no está disponible'], 403);
        }

        // Verificar si ya existe una respuesta
        $existingResponse = StudentResponse::where('assessment_id', $assessment->id)
            ->where('student_id', $request->user()->id)
            ->first();

        if ($existingResponse) {
            if ($existingResponse->completed_at) {
                return response()->json([
                    'message' => 'Ya has completado esta evaluación',
                    'response' => $existingResponse,
                ], 400);
            }

            // Si existe pero no está completada, continuar con ella
            return response()->json([
                'message' => 'Continuando evaluación',
                'response' => $existingResponse,
            ]);
        }

        // Crear nueva respuesta
        $response = StudentResponse::create([
            'assessment_id' => $assessment->id,
            'student_id' => $request->user()->id,
            'responses' => [],
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Evaluación iniciada',
            'response' => $response,
        ], 201);
    }

    /**
     * Guardar respuesta (puede ser parcial o completa)
     */
    public function save(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar que es estudiante
        if (!$request->user()->isStudent()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'responses' => 'required|array',
            'is_final' => 'sometimes|boolean',
        ]);

        // Buscar o crear respuesta
        $studentResponse = StudentResponse::where('assessment_id', $assessment->id)
            ->where('student_id', $request->user()->id)
            ->first();

        if (!$studentResponse) {
            $studentResponse = StudentResponse::create([
                'assessment_id' => $assessment->id,
                'student_id' => $request->user()->id,
                'responses' => $validated['responses'],
                'started_at' => now(),
            ]);
        } else {
            $studentResponse->update([
                'responses' => $validated['responses'],
            ]);
        }

        // Si es el envío final
        if (isset($validated['is_final']) && $validated['is_final']) {
            $timeSpent = $studentResponse->started_at
                ? (int) now()->diffInSeconds($studentResponse->started_at)
                : null;

            // Determinar estado de calificación
            $gradingService = app(GradingService::class);
            $gradingStatus = $gradingService->determineGradingStatus($assessment);

            $studentResponse->update([
                'completed_at' => now(),
                'time_spent' => $timeSpent,
                'grading_status' => $gradingStatus,
            ]);

            // Calcular score de preguntas auto-calificables (multiple choice con correct_answer)
            // Se calcula siempre: para auto_graded es el score final,
            // para pending_grading es un score parcial de la sección MC
            // que será sobreescrito por calculateTotalScore() cuando el instructor califique
            $score = $studentResponse->calculateScore();
            if ($score !== null) {
                $studentResponse->update(['score' => $score]);
            }

            $responseMessage = $gradingStatus === 'pending_grading'
                ? 'Evaluación completada. Pendiente de calificación por el instructor.'
                : 'Evaluación completada';

            return response()->json([
                'message' => $responseMessage,
                'response' => $studentResponse->fresh(),
                'grading_status' => $gradingStatus,
            ]);
        }

        return response()->json([
            'message' => 'Respuesta guardada',
            'response' => $studentResponse,
        ]);
    }

    /**
     * Obtener respuesta del estudiante
     */
    public function show(Request $request, Course $course, Assessment $assessment)
    {
        $response = StudentResponse::where('assessment_id', $assessment->id)
            ->where('student_id', $request->user()->id)
            ->with('assessment')
            ->first();

        if (!$response) {
            return response()->json(['message' => 'No se ha iniciado esta evaluación'], 404);
        }

        return response()->json([
            'response' => $response,
        ]);
    }

    /**
     * Listar todas las respuestas de un estudiante en un curso
     */
    public function myResponses(Request $request, Course $course)
    {
        $responses = StudentResponse::whereHas('assessment', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->where('student_id', $request->user()->id)
            ->with('assessment')
            ->get();

        return response()->json([
            'responses' => $responses,
        ]);
    }

    /**
     * Ver todas las respuestas de una evaluación (instructor)
     */
    public function assessmentResponses(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $responses = StudentResponse::where('assessment_id', $assessment->id)
            ->with('student:id,name,email')
            ->get();

        return response()->json([
            'responses' => $responses,
            'stats' => [
                'total' => $responses->count(),
                'completed' => $responses->whereNotNull('completed_at')->count(),
                'average_score' => $responses->whereNotNull('score')->avg('score'),
                'average_time' => $responses->whereNotNull('time_spent')->avg('time_spent'),
            ],
        ]);
    }
}
