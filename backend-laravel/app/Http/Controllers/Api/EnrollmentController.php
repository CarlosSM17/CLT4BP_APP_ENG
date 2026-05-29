<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Inscribir estudiantes a un curso
     */
    public function enroll(Request $request, Course $course)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado para inscribir estudiantes',
            ], 403);
        }

        // Verificar que el instructor sea dueño del curso
        if ($request->user()->isInstructor() && $course->instructor_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No puedes inscribir estudiantes en cursos de otros instructores',
            ], 403);
        }

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $enrolled = [];
        $alreadyEnrolled = [];

        foreach ($validated['student_ids'] as $studentId) {
            // Verificar que el usuario sea estudiante
            $student = User::find($studentId);
            if (!$student->isStudent()) {
                continue;
            }

            // Verificar si ya está inscrito
            $exists = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $studentId)
                ->exists();

            if ($exists) {
                $alreadyEnrolled[] = $student->name;
                continue;
            }

            // Inscribir
            CourseEnrollment::create([
                'course_id' => $course->id,
                'student_id' => $studentId,
                'status' => 'enrolled',
            ]);

            $enrolled[] = $student->name;
        }

        return response()->json([
            'message' => 'Inscripción procesada',
            'enrolled' => $enrolled,
            'already_enrolled' => $alreadyEnrolled,
        ]);
    }

    /**
     * Obtener estudiantes de un curso
     */
    public function getStudents(Course $course)
    {
        $students = $course->students()
            ->withPivot('status', 'enrollment_date', 'completion_date')
            ->get();

        return response()->json([
            'students' => $students,
        ]);
    }

    /**
     * Remover estudiante de un curso
     */
    public function unenroll(Request $request, Course $course, User $student)
    {
        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar que el instructor sea dueño del curso
        if ($request->user()->isInstructor() && $course->instructor_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        $enrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'El estudiante no está inscrito en este curso',
            ], 404);
        }

        $enrollment->update(['status' => 'dropped']);

        return response()->json([
            'message' => 'Estudiante removido del curso',
        ]);
    }

    /**
     * Obtener cursos de un estudiante
     */
    public function myEnrollments(Request $request)
    {
        $enrollments = CourseEnrollment::with(['course.instructor'])
            ->where('student_id', $request->user()->id)
            ->where('status', 'enrolled')
            ->latest()
            ->get();

        return response()->json([
            'enrollments' => $enrollments,
        ]);
    }
}
