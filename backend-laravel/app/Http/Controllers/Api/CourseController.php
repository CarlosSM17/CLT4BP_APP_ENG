<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Listar cursos
     *
     * @param \Illuminate\Http\Request $request
     */
    public function index(Request $request)
    {
        $query = Course::with(['instructor:id,name,email', 'enrollments']);

        // Filtro por instructor (para instructores ver solo sus cursos)
        if ($request->user()->isInstructor()) {
            $query->byInstructor($request->user()->id);
        }

        // Filtro por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Búsqueda
        if ($request->has('search')) {
            $query->search($request->search);
        }

        $courses = $query->latest()->get();

        // Agregar conteo de estudiantes a cada curso
        $courses->each(function ($course) {
            $course->students_count = $course->enrolledStudentsCount();
        });

        return response()->json([
            'courses' => $courses,
        ]);
    }

    /**
     * Crear curso
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'learning_objectives' => 'nullable|array',
            'learning_objectives.*' => 'string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:draft,active,inactive,completed',
        ]);

        // Si es instructor, asignar su ID
        if ($request->user()->isInstructor()) {
            $validated['instructor_id'] = $request->user()->id;
        }
        // Si es admin, puede especificar el instructor
        elseif ($request->user()->isAdmin() && $request->has('instructor_id')) {
            $validated['instructor_id'] = $request->instructor_id;
        }
        else {
            return response()->json([
                'message' => 'No autorizado para crear cursos',
            ], 403);
        }

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Curso creado exitosamente',
            'course' => $course->load('instructor:id,name,email'),
        ], 201);
    }

    /**
     * Ver curso específico
     */
    public function show(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$this->canAccessCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado para ver este curso',
            ], 403);
        }

        $course->load(['instructor:id,name,email', 'students:id,name,email']);
        $course->students_count = $course->enrolledStudentsCount();

        return response()->json([
            'course' => $course,
        ]);
    }

    /**
     * Actualizar curso
     */
    public function update(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$this->canModifyCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado para modificar este curso',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'learning_objectives' => 'nullable|array',
            'learning_objectives.*' => 'string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:draft,active,inactive,completed',
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Curso actualizado exitosamente',
            'course' => $course->load('instructor:id,name,email'),
        ]);
    }

    /**
     * Eliminar curso
     */
    public function destroy(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$this->canModifyCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'No autorizado para eliminar este curso',
            ], 403);
        }

        $course->delete();

        return response()->json([
            'message' => 'Curso eliminado exitosamente',
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

    /**
     * Verificar si el usuario puede modificar el curso
     */
    private function canModifyCourse($user, $course): bool
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
