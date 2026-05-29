<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Listar evaluaciones de un curso
     */
    public function index(Request $request, Course $course)
    {
        // Verificar permisos de acceso al curso
        if (!$this->canAccessCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado para ver este curso',
            ], 403);
        }

        $assessmentsQuery = $course->assessments()
            ->with(['responses' => function($query) use ($request) {
                if ($request->user()->isStudent()) {
                    $query->where('student_id', $request->user()->id);
                }
            }]);

        // Los estudiantes solo ven evaluaciones activas
        if ($request->user()->isStudent()) {
            $assessmentsQuery->where('is_active', true);
        }

        $assessments = $assessmentsQuery->get();

        // Agregar información de completitud para cada evaluación
        $assessments->each(function ($assessment) use ($request) {
            if ($request->user()->isStudent()) {
                $assessment->is_completed = $assessment->isCompleted($request->user()->id);
                $assessment->user_response = $assessment->getResponse($request->user()->id);
            }
            $assessment->completion_rate = $assessment->completionRate();
        });

        return response()->json([
            'assessments' => $assessments,
        ]);
    }

    /**
     * Crear evaluación
     */
    public function store(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'assessment_type' => 'required|in:recall_initial,comprehension_initial,mslq_motivation_initial,mslq_strategies,recall_final,comprehension_final,cognitive_load,mslq_motivation_final,course_interest,imms',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.id' => 'required',
            'questions.*.type' => 'required|in:multiple_choice,text,scale,likert',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'nullable|array',
            'config' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'time_limit' => 'nullable|integer|min:1',
        ]);

        $validated['course_id'] = $course->id;

        $assessment = Assessment::create($validated);

        return response()->json([
            'message' => 'Evaluación creada exitosamente',
            'assessment' => $assessment,
        ], 201);
    }

    /**
     * Ver evaluación específica
     */
    public function show(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar que la evaluación pertenece al curso
        if ($assessment->course_id !== $course->id) {
            return response()->json(['message' => 'Evaluación no encontrada'], 404);
        }

        // Verificar permisos de acceso al curso
        if (!$this->canAccessCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado para ver este curso',
            ], 403);
        }

        // Si es estudiante y la evaluación no está activa, no puede verla
        if ($request->user()->isStudent() && !$assessment->is_active) {
            return response()->json([
                'message' => 'Esta evaluación no está disponible',
            ], 403);
        }

        $assessment->load('responses');

        // Si es estudiante, agregar su respuesta
        if ($request->user()->isStudent()) {
            $assessment->is_completed = $assessment->isCompleted($request->user()->id);
            $assessment->user_response = $assessment->getResponse($request->user()->id);
        }

        return response()->json([
            'assessment' => $assessment,
        ]);
    }

    /**
     * Actualizar evaluación
     */
    public function update(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verificar que la evaluación pertenece al curso
        if ($assessment->course_id !== $course->id) {
            return response()->json(['message' => 'Evaluación no encontrada'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'sometimes|required|array',
            'config' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'time_limit' => 'nullable|integer|min:1',
        ]);

        $assessment->update($validated);

        return response()->json([
            'message' => 'Evaluación actualizada exitosamente',
            'assessment' => $assessment,
        ]);
    }

    /**
     * Eliminar evaluación
     */
    public function destroy(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verificar que la evaluación pertenece al curso
        if ($assessment->course_id !== $course->id) {
            return response()->json(['message' => 'Evaluación no encontrada'], 404);
        }

        $assessment->delete();

        return response()->json([
            'message' => 'Evaluación eliminada exitosamente',
        ]);
    }

    /**
     * Activar/Desactivar evaluación
     */
    public function toggleActive(Request $request, Course $course, Assessment $assessment)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $assessment->update([
            'is_active' => !$assessment->is_active,
        ]);

        return response()->json([
            'message' => 'Estado actualizado',
            'assessment' => $assessment,
        ]);
    }

    /**
     * Verificar si el usuario puede acceder al curso
     */
    private function canAccessCourse($user, $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isInstructor() && $course->instructor_id === $user->id) {
            return true;
        }

        if ($user->isStudent()) {
            // Verificar si el estudiante está inscrito en el curso
            return $course->enrollments()
                ->where('student_id', $user->id)
                ->where('status', 'enrolled')
                ->exists();
        }

        return false;
    }
}
