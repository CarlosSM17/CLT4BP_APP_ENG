<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\GroupProfile;
use App\Models\StudentProfile;
use App\Models\StudentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    /**
     * Dashboard de reportes para el instructor (datos del grupo completo)
     */
    public function instructorReport(Request $request, int $courseId)
    {
        $course = Course::findOrFail($courseId);

        // Verificar permisos
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Cargar perfil grupal
        $groupProfile = GroupProfile::where('course_id', $courseId)->first();
        $groupProfileData = null;
        if ($groupProfile) {
            $groupProfileData = array_merge($groupProfile->profile_data, [
                'student_count' => $groupProfile->student_count,
                'generated_at'  => $groupProfile->generated_at,
            ]);
        }

        // Cargar perfiles individuales
        $studentProfiles = StudentProfile::where('course_id', $courseId)
            ->with('student:id,name,email')
            ->get()
            ->map(function ($profile) {
                $data = $profile->profile_data ?? [];
                return [
                    'student_id'         => $profile->student_id,
                    'name'               => $profile->student->name ?? 'N/A',
                    'overall_motivation' => $data['profile_summary']['overall_motivation'] ?? null,
                    'overall_strategies' => $data['profile_summary']['overall_strategies'] ?? null,
                    'prior_knowledge'    => $data['profile_summary']['prior_knowledge'] ?? null,
                    'mslq_scores'        => $data['mslq_scores'] ?? [],
                    'knowledge'          => $data['knowledge_assessment'] ?? [],
                    'generated_at'       => $profile->generated_at,
                ];
            });

        // Computar scores de evaluaciones adicionales (todos los estudiantes)
        $cognitiveLoad  = $this->computeAssessmentScores($courseId, 'cognitive_load');
        $courseInterest = $this->computeAssessmentScores($courseId, 'course_interest');
        $imms           = $this->computeAssessmentScores($courseId, 'imms');

        // Comparación pre/post grupal
        $prePost = $this->computeGroupPrePost($courseId);

        return response()->json([
            'group_profile'   => $groupProfileData,
            'cognitive_load'  => $cognitiveLoad,
            'course_interest' => $courseInterest,
            'imms'            => $imms,
            'pre_post'        => $prePost,
            'student_profiles' => $studentProfiles,
        ]);
    }

    /**
     * Reporte personal del estudiante
     */
    public function studentReport(Request $request, int $courseId)
    {
        $course  = Course::findOrFail($courseId);
        $student = $request->user();

        // Verificar inscripción
        $isEnrolled = $course->students()->where('student_id', $student->id)->exists();
        if (!$isEnrolled) {
            return response()->json(['message' => 'No estás inscrito en este curso'], 403);
        }

        // Cargar perfil individual
        $studentProfile = StudentProfile::where('course_id', $courseId)
            ->where('student_id', $student->id)
            ->first();

        if (!$studentProfile) {
            return response()->json([
                'has_profile'   => false,
                'message'       => 'Tu perfil aún no ha sido generado. Completa las evaluaciones iniciales.',
            ]);
        }

        $profileData = $studentProfile->profile_data ?? [];

        // Promedios del grupo para comparación
        $groupProfile    = GroupProfile::where('course_id', $courseId)->first();
        $groupAverages   = $groupProfile ? ($groupProfile->profile_data['mslq_averages'] ?? []) : [];

        // Scores adicionales del estudiante
        $cognitiveLoad  = $this->computeAssessmentScores($courseId, 'cognitive_load', $student->id);
        $courseInterest = $this->computeAssessmentScores($courseId, 'course_interest', $student->id);
        $imms           = $this->computeAssessmentScores($courseId, 'imms', $student->id);

        // Pre/post personal
        $prePost = $this->computeStudentPrePost($courseId, $student->id);

        return response()->json([
            'has_profile'    => true,
            'profile'        => [
                'mslq_scores'        => $profileData['mslq_scores'] ?? [],
                'knowledge'          => $profileData['knowledge_assessment'] ?? [],
                'profile_summary'    => $profileData['profile_summary'] ?? [],
                'recommendations'    => $profileData['recommendations'] ?? [],
            ],
            'group_averages'  => $groupAverages,
            'cognitive_load'  => $cognitiveLoad,
            'course_interest' => $courseInterest,
            'imms'            => $imms,
            'pre_post'        => $prePost,
        ]);
    }

    /**
     * Calcula promedios por dimensión de un assessment tipo Likert para un curso.
     * Si $studentId es null, promedia sobre todos los estudiantes.
     */
    private function computeAssessmentScores(int $courseId, string $type, ?int $studentId = null): array
    {
        $assessment = Assessment::where('course_id', $courseId)
            ->where('assessment_type', $type)
            ->where('is_template', false)
            ->first();

        if (!$assessment || empty($assessment->questions)) {
            return ['available' => false];
        }

        // Obtener respuestas
        $query = StudentResponse::where('assessment_id', $assessment->id);
        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }
        $responses = $query->whereNotNull('responses')->get();

        if ($responses->isEmpty()) {
            return ['available' => false];
        }

        // Agrupar preguntas por dimensión
        $questionsByDimension = collect($assessment->questions)
            ->groupBy('dimension')
            ->filter(fn($qs, $dim) => !empty($dim));

        // Acumuladores por dimensión
        $dimensionTotals = [];
        $dimensionCounts = [];
        $overallTotal    = 0;
        $overallCount    = 0;

        foreach ($responses as $response) {
            $answers = $response->responses ?? [];
            foreach ($questionsByDimension as $dim => $questions) {
                $dimTotal = 0;
                $dimCount = 0;
                foreach ($questions as $q) {
                    $qId = $q['id'] ?? null;
                    if ($qId !== null && isset($answers[$qId]) && is_numeric($answers[$qId])) {
                        $dimTotal += (float) $answers[$qId];
                        $dimCount++;
                        $overallTotal++;
                        $overallCount++;
                    }
                }
                if ($dimCount > 0) {
                    $dimensionTotals[$dim] = ($dimensionTotals[$dim] ?? 0) + ($dimTotal / $dimCount);
                    $dimensionCounts[$dim] = ($dimensionCounts[$dim] ?? 0) + 1;
                }
            }
        }

        // Calcular promedios finales
        $dimensionAverages = [];
        foreach ($dimensionTotals as $dim => $total) {
            $count = $dimensionCounts[$dim];
            $dimensionAverages[$dim] = round($total / $count, 2);
        }

        $overallAvg = $overallCount > 0 ? round($overallTotal / $overallCount, 2) : 0;

        return [
            'available'   => true,
            'dimensions'  => $dimensionAverages,
            'overall'     => $overallAvg,
            'respondents' => $responses->count(),
        ];
    }

    /**
     * Calcula comparación pre/post para el grupo completo.
     * Pares: recall, comprehension, mslq_motivation
     */
    private function computeGroupPrePost(int $courseId): array
    {
        $pairs = [
            'recall'          => ['initial' => 'recall_initial',          'final' => 'recall_final'],
            'comprehension'   => ['initial' => 'comprehension_initial',   'final' => 'comprehension_final'],
            'mslq_motivation' => ['initial' => 'mslq_motivation_initial', 'final' => 'mslq_motivation_final'],
        ];

        $result = [];

        foreach ($pairs as $key => $pair) {
            $initialAssessment = Assessment::where('course_id', $courseId)
                ->where('assessment_type', $pair['initial'])
                ->where('is_template', false)
                ->first();

            $finalAssessment = Assessment::where('course_id', $courseId)
                ->where('assessment_type', $pair['final'])
                ->where('is_template', false)
                ->first();

            if (!$initialAssessment || !$finalAssessment) {
                $result[$key] = ['available' => false];
                continue;
            }

            $initialScores = StudentResponse::where('assessment_id', $initialAssessment->id)
                ->whereNotNull('score')
                ->pluck('score', 'student_id');

            $finalScores = StudentResponse::where('assessment_id', $finalAssessment->id)
                ->whereNotNull('score')
                ->pluck('score', 'student_id');

            // Solo estudiantes con ambas respuestas
            $commonIds = $initialScores->keys()->intersect($finalScores->keys());

            if ($commonIds->isEmpty()) {
                $result[$key] = ['available' => false];
                continue;
            }

            $initialAvg = $commonIds->map(fn($id) => (float) $initialScores[$id])->avg();
            $finalAvg   = $commonIds->map(fn($id) => (float) $finalScores[$id])->avg();

            $result[$key] = [
                'available'   => true,
                'initial_avg' => round($initialAvg, 2),
                'final_avg'   => round($finalAvg, 2),
                'change'      => round($finalAvg - $initialAvg, 2),
                'students'    => $commonIds->count(),
            ];
        }

        return $result;
    }

    /**
     * Calcula comparación pre/post para un estudiante específico.
     */
    private function computeStudentPrePost(int $courseId, int $studentId): array
    {
        $pairs = [
            'recall'        => ['initial' => 'recall_initial',        'final' => 'recall_final'],
            'comprehension' => ['initial' => 'comprehension_initial', 'final' => 'comprehension_final'],
        ];

        $result = [];

        foreach ($pairs as $key => $pair) {
            $initialAssessment = Assessment::where('course_id', $courseId)
                ->where('assessment_type', $pair['initial'])
                ->where('is_template', false)
                ->first();

            $finalAssessment = Assessment::where('course_id', $courseId)
                ->where('assessment_type', $pair['final'])
                ->where('is_template', false)
                ->first();

            if (!$initialAssessment || !$finalAssessment) {
                $result[$key] = ['available' => false];
                continue;
            }

            $initialResponse = StudentResponse::where('assessment_id', $initialAssessment->id)
                ->where('student_id', $studentId)
                ->first();

            $finalResponse = StudentResponse::where('assessment_id', $finalAssessment->id)
                ->where('student_id', $studentId)
                ->first();

            if (!$initialResponse) {
                $result[$key] = ['available' => false];
                continue;
            }

            $initial = (float) ($initialResponse->score ?? 0);

            if (!$finalResponse) {
                $result[$key] = [
                    'available' => false,
                    'initial'   => round($initial, 2),
                ];
                continue;
            }

            $final = (float) ($finalResponse->score ?? 0);

            $result[$key] = [
                'available' => true,
                'initial'   => round($initial, 2),
                'final'     => round($final, 2),
                'change'    => round($final - $initial, 2),
            ];
        }

        return $result;
    }
}
