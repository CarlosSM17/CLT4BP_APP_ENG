<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudentProfile;
use App\Models\GroupProfile;
use App\Services\StudentProfileGeneratorService;
use App\Services\GroupProfileGeneratorService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private StudentProfileGeneratorService $studentProfileGenerator;
    private GroupProfileGeneratorService $groupProfileGenerator;

    public function __construct(
        StudentProfileGeneratorService $studentProfileGenerator,
        GroupProfileGeneratorService $groupProfileGenerator
    ) {
        $this->studentProfileGenerator = $studentProfileGenerator;
        $this->groupProfileGenerator = $groupProfileGenerator;
    }

    /**
     * Generar perfil individual de un estudiante
     */
    public function generateStudentProfile(Request $request, Course $course, int $studentId)
    {
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            $profile = $this->studentProfileGenerator->generateProfile($studentId, $course->id);

            return response()->json([
                'success' => true,
                'message' => 'Perfil generado exitosamente',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar perfiles de todos los estudiantes del curso
     */
    public function generateAllStudentProfiles(Request $request, Course $course)
    {
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $students = $course->students;
        $generated = [];
        $errors = [];

        foreach ($students as $student) {
            try {
                $profile = $this->studentProfileGenerator->generateProfile(
                    $student->id,
                    $course->id
                );
                $generated[] = $profile;
            } catch (\Exception $e) {
                $errors[] = [
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'generated_count' => count($generated),
            'errors_count' => count($errors),
            'profiles' => $generated,
            'errors' => $errors
        ]);
    }

    /**
     * Generar perfil grupal
     */
    public function generateGroupProfile(Request $request, Course $course)
    {
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            $groupProfile = $this->groupProfileGenerator->generateGroupProfile($course->id);

            return response()->json([
                'success' => true,
                'message' => 'Perfil grupal generado exitosamente',
                'data' => $groupProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar perfil grupal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener perfil individual
     */
    public function getStudentProfile(Request $request, Course $course, int $studentId)
    {
        // Estudiantes pueden ver su propio perfil
        if ($request->user()->isStudent()) {
            if ($request->user()->id !== $studentId) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        } elseif (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $profile = StudentProfile::where('course_id', $course->id)
            ->where('student_id', $studentId)
            ->with('student:id,name,email')
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * Obtener todos los perfiles del curso
     */
    public function getCourseProfiles(Request $request, Course $course)
    {
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $profiles = StudentProfile::where('course_id', $course->id)
            ->with('student:id,name,email')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $profiles
        ]);
    }

    /**
     * Obtener perfil grupal
     */
    public function getGroupProfile(Request $request, Course $course)
    {
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $groupProfile = GroupProfile::where('course_id', $course->id)->first();

        if (!$groupProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil grupal no encontrado. Genera los perfiles individuales primero.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $groupProfile
        ]);
    }

    /**
     * Regenerar todos los perfiles (individual y grupal)
     */
    public function regenerateAllProfiles(Request $request, Course $course)
    {
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            $students = $course->students;
            $generated = 0;
            $errors = [];

            // Generar perfiles individuales
            foreach ($students as $student) {
                try {
                    $this->studentProfileGenerator->generateProfile($student->id, $course->id);
                    $generated++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Generar perfil grupal si hay perfiles generados
            $groupProfile = null;
            if ($generated > 0) {
                try {
                    $groupProfile = $this->groupProfileGenerator->generateGroupProfile($course->id);
                } catch (\Exception $e) {
                    $errors[] = [
                        'type' => 'group_profile',
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Perfiles regenerados exitosamente',
                'student_profiles_count' => $generated,
                'group_profile_generated' => $groupProfile !== null,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar perfiles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si el usuario puede gestionar el curso
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
