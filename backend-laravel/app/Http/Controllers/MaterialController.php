<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\StudentProfile;
use App\Models\GroupProfile;
use App\Models\CltEffectsSelection;
use App\Models\InstructionalMaterial;
use App\Models\MaterialAccessLog;
use App\Services\AiServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    private AiServiceClient $aiService;

    public function __construct(AiServiceClient $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Genera material instruccional
     */
    public function generate(Request $request, int $courseId)
    {
        $this->authorize('manage', Course::findOrFail($courseId));

        $validated = $request->validate([
            'material_type' => 'required|in:learning_tasks,support_info,procedural_info,verbal_protocols,example',
            'profile_type' => 'required|in:individual,group',
            'student_id' => 'required_if:profile_type,individual|nullable|exists:users,id',
            'topic' => 'required|string|max:255',
            'additional_context' => 'nullable|string'
        ]);

        try {
            // Obtener curso con objetivos
            $course = Course::findOrFail($courseId);

            // Obtener perfil
            if ($validated['profile_type'] === 'individual') {
                $profile = StudentProfile::where('course_id', $courseId)
                    ->where('student_id', $validated['student_id'])
                    ->firstOrFail();
                $profileData = $profile->profile_data;
            } else {
                $profile = GroupProfile::where('course_id', $courseId)->firstOrFail();
                $profileData = $profile->profile_data;
                $profileData['student_count'] = $profile->student_count;
            }

            // Obtener efectos CLT seleccionados
            $cltSelection = CltEffectsSelection::where('course_id', $courseId)->first();
            if (!$cltSelection) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must preselect CLT effects first'
                ], 400);
            }

            // Preparar request para el servicio de IA
            $aiRequest = [
                'course_id' => $courseId,
                'profile_type' => $validated['profile_type'],
                'profile_data' => $profileData,
                'student_id' => $validated['student_id'] ?? null,
                'learning_objectives' => $this->formatLearningObjectives($course),
                'selected_clt_effects' => $cltSelection->selected_effects,
                'material_type' => $validated['material_type'],
                'topic' => $validated['topic'],
                'additional_context' => $validated['additional_context'] ?? null
            ];

            // Generar material con IA
            Log::info('Generating instructional material', [
                'course_id' => $courseId,
                'material_type' => $validated['material_type']
            ]);

            $result = $this->aiService->generateMaterial($aiRequest);

            // Guardar material en BD (desactivado por defecto)
            $material = InstructionalMaterial::create([
                'course_id' => $courseId,
                'material_type' => $validated['material_type'],
                'target_type' => $validated['profile_type'],
                'target_student_id' => $validated['student_id'] ?? null,
                'content' => $result['content'],
                'is_active' => false,
                'timer_seconds' => $this->getDefaultTimer($validated['material_type'])
            ]);

            Log::info('Instructional material saved successfully', [
                'material_id' => $material->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Instructional material generated successfully',
                'data' => [
                    'material' => $material,
                    'token_usage' => $result['token_usage'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating instructional material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating instructional material: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatLearningObjectives(Course $course): array
    {
        $objectives = $course->learning_objectives ?? [];

        $formatted = [];
        foreach ($objectives as $index => $objective) {
            $formatted[] = [
                'id' => $index + 1,
                'description' => is_string($objective) ? $objective : $objective['description'],
                'bloom_level' => is_array($objective) ? ($objective['bloom_level'] ?? null) : null
            ];
        }

        return $formatted;
    }

    private function getDefaultTimer(string $materialType): ?int
    {
        // Timer solo para información de soporte
        return $materialType === 'support_info' ? 1800 : null; // 30 minutos
    }

    /**
     * Obtener material generado
     */
    public function getMaterial(Request $request, int $courseId, int $materialId)
    {
        $material = InstructionalMaterial::where('course_id', $courseId)
            ->findOrFail($materialId);

        // Verificar permisos
        $user = $request->user();
        if ($user->role === 'student') {
            // Estudiantes solo ven material activo y dirigido a ellos
            if (!$material->is_active) {
                abort(403, 'This material is not available yet');
            }

            if ($material->target_type === 'individual'
                && $material->target_student_id !== $user->id) {
                abort(403, 'You do not have access to this material');
            }
        }

        return response()->json([
            'success' => true,
            'data' => $material
        ]);
    }

    /**
     * Listar materiales del curso (instructor)
     */
    public function listMaterials(Request $request, int $courseId)
    {
        $this->authorize('manage', Course::findOrFail($courseId));

        $materials = InstructionalMaterial::where('course_id', $courseId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    /**
     * Activar/Desactivar material instruccional
     */
    public function toggleActive(Request $request, int $courseId, int $materialId)
    {
        $this->authorize('manage', Course::findOrFail($courseId));

        $material = InstructionalMaterial::where('course_id', $courseId)
            ->findOrFail($materialId);

        $newState = !$material->is_active;

        $updateData = ['is_active' => $newState];

        if ($newState) {
            $updateData['activated_at'] = now();
            $updateData['deactivated_at'] = null;
        } else {
            $updateData['deactivated_at'] = now();
        }

        $material->update($updateData);

        return response()->json([
            'success' => true,
            'message' => $newState ? 'Material activated' : 'Material deactivated',
            'data' => $material->fresh()
        ]);
    }

    /**
     * Actualizar timer del material
     */
    public function updateTimer(Request $request, int $courseId, int $materialId)
    {
        $this->authorize('manage', Course::findOrFail($courseId));

        $material = InstructionalMaterial::where('course_id', $courseId)
            ->findOrFail($materialId);

        $validated = $request->validate([
            'timer_seconds' => 'required|integer|min:60|max:7200'
        ]);

        $material->update(['timer_seconds' => $validated['timer_seconds']]);

        return response()->json([
            'success' => true,
            'message' => 'Updated timer',
            'data' => $material
        ]);
    }

    /**
     * Listar materiales activos para un estudiante
     */
    public function studentMaterials(Request $request, int $courseId)
    {
        $user = $request->user();
        $course = Course::findOrFail($courseId);

        $isEnrolled = $course->students()->where('users.id', $user->id)->exists();
        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        $materials = InstructionalMaterial::where('course_id', $courseId)
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'group')
                      ->orWhere('target_student_id', $user->id);
            })
            ->orderBy('material_type')
            ->orderBy('activated_at', 'desc')
            ->get();

        $materials->each(function ($material) use ($user) {
            $lastAccess = $material->accessLogs()
                ->where('student_id', $user->id)
                ->latest('started_at')
                ->first();
            $material->last_access = $lastAccess;
            $material->total_time_spent = $material->accessLogs()
                ->where('student_id', $user->id)
                ->sum('time_spent_seconds');
        });

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    /**
     * Registrar inicio de acceso a material
     */
    public function logAccessStart(Request $request, int $courseId, int $materialId)
    {
        $user = $request->user();
        $material = InstructionalMaterial::where('course_id', $courseId)
            ->where('is_active', true)
            ->findOrFail($materialId);

        if ($material->target_type === 'individual' && $material->target_student_id !== $user->id) {
            abort(403, 'You do not have access to this material');
        }

        $log = MaterialAccessLog::create([
            'material_id' => $materialId,
            'student_id' => $user->id,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $log
        ], 201);
    }

    /**
     * Registrar fin de acceso a material
     */
    public function logAccessComplete(Request $request, int $courseId, int $materialId)
    {
        $user = $request->user();

        $validated = $request->validate([
            'log_id' => 'required|integer',
            'time_spent_seconds' => 'required|integer|min:1'
        ]);

        $log = MaterialAccessLog::where('material_id', $materialId)
            ->where('student_id', $user->id)
            ->where('id', $validated['log_id'])
            ->whereNull('completed_at')
            ->firstOrFail();

        $log->update([
            'completed_at' => now(),
            'time_spent_seconds' => $validated['time_spent_seconds']
        ]);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * Ver logs de acceso de un material (instructor)
     */
    public function getAccessLogs(Request $request, int $courseId, int $materialId)
    {
        $this->authorize('manage', Course::findOrFail($courseId));

        $logs = MaterialAccessLog::where('material_id', $materialId)
            ->with('student:id,name,email')
            ->orderBy('started_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
