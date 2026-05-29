<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\StudentResponse;
use App\Models\MaterialAccessLog;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataCollectionController extends Controller
{
    /**
     * Matriz de completitud: cada estudiante x cada tipo de evaluación
     */
    public function summary(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('manage', $course);

        $students = $course->students()->select('users.id', 'users.name', 'users.email')->get();
        $assessments = $course->assessments()->where('is_template', false)->get();

        $assessmentsByType = $assessments->groupBy('assessment_type');
        $assessmentTypes = $assessmentsByType->keys()->toArray();

        // Sin filtro por completed_at: igual que el ProfileGenerator, contamos
        // cualquier respuesta que exista (auto-guardada o enviada formalmente).
        $allResponses = StudentResponse::whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $studentsData = $students->map(function ($student) use ($assessmentsByType, $allResponses) {
            $studentResponses = $allResponses->get($student->id, collect());
            $assessmentStatus = [];

            foreach ($assessmentsByType as $type => $typeAssessments) {
                $response = $studentResponses->first(function ($r) use ($typeAssessments) {
                    return $typeAssessments->pluck('id')->contains($r->assessment_id);
                });

                $assessmentStatus[$type] = $response
                    ? [
                        'completed' => true,
                        'submitted' => !is_null($response->completed_at),
                        'score' => $response->score,
                        'response_id' => $response->id,
                        'completed_at' => $response->completed_at,
                    ]
                    : ['completed' => false, 'submitted' => false];
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'assessments' => $assessmentStatus,
            ];
        });

        $completionRates = [];
        $totalStudents = $students->count();
        foreach ($assessmentsByType as $type => $typeAssessments) {
            $completedCount = $studentsData->filter(function ($s) use ($type) {
                return $s['assessments'][$type]['completed'] ?? false;
            })->count();
            $completionRates[$type] = $totalStudents > 0
                ? round(($completedCount / $totalStudents) * 100, 1)
                : 0;
        }

        return response()->json([
            'students' => $studentsData->values(),
            'completion_rates' => $completionRates,
            'total_students' => $totalStudents,
            'assessment_types' => $assessmentTypes,
        ]);
    }

    /**
     * Comparación pre/post: initial vs final para recall, comprehension, MSLQ motivación
     */
    public function prePostComparison(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('manage', $course);

        $pairs = [
            ['label' => 'Recuerdo (Recall)', 'initial' => 'recall_initial', 'final' => 'recall_final'],
            ['label' => 'Comprensión', 'initial' => 'comprehension_initial', 'final' => 'comprehension_final'],
            ['label' => 'MSLQ Motivación', 'initial' => 'mslq_motivation_initial', 'final' => 'mslq_motivation_final'],
        ];

        $students = $course->students()->select('users.id', 'users.name')->get();
        $assessments = $course->assessments()->where('is_template', false)->get();

        $results = [];

        foreach ($pairs as $pair) {
            $initialAssessment = $assessments->firstWhere('assessment_type', $pair['initial']);
            $finalAssessment = $assessments->firstWhere('assessment_type', $pair['final']);

            $pairData = [
                'label' => $pair['label'],
                'initial_type' => $pair['initial'],
                'final_type' => $pair['final'],
                'students' => [],
                'averages' => ['initial' => null, 'final' => null, 'change' => null],
            ];

            if (!$initialAssessment || !$finalAssessment) {
                $results[] = $pairData;
                continue;
            }

            $initialResponses = StudentResponse::where('assessment_id', $initialAssessment->id)
                ->get()
                ->keyBy('student_id');

            $finalResponses = StudentResponse::where('assessment_id', $finalAssessment->id)
                ->get()
                ->keyBy('student_id');

            $studentComparisons = [];
            $totalInitial = 0;
            $totalFinal = 0;
            $count = 0;

            foreach ($students as $student) {
                $initial = $initialResponses->get($student->id);
                $final = $finalResponses->get($student->id);

                if ($initial && $final) {
                    $initialScore = $this->getEffectiveScore($initial, $initialAssessment);
                    $finalScore = $this->getEffectiveScore($final, $finalAssessment);
                    $change = $finalScore !== null && $initialScore !== null
                        ? round($finalScore - $initialScore, 2)
                        : null;

                    $studentComparisons[] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'initial_score' => $initialScore,
                        'final_score' => $finalScore,
                        'change' => $change,
                    ];

                    if ($initialScore !== null && $finalScore !== null) {
                        $totalInitial += $initialScore;
                        $totalFinal += $finalScore;
                        $count++;
                    }
                }
            }

            $pairData['students'] = $studentComparisons;
            if ($count > 0) {
                $avgInitial = round($totalInitial / $count, 2);
                $avgFinal = round($totalFinal / $count, 2);
                $pairData['averages'] = [
                    'initial' => $avgInitial,
                    'final' => $avgFinal,
                    'change' => round($avgFinal - $avgInitial, 2),
                ];
            }

            $results[] = $pairData;
        }

        return response()->json(['pairs' => $results]);
    }

    /**
     * Exportar todos los datos del curso como CSV
     */
    public function exportCsv(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('manage', $course);

        $students = $course->students()->select('users.id', 'users.name', 'users.email')->get();
        $assessments = $course->assessments()->where('is_template', false)->get();

        $responses = StudentResponse::whereIn('assessment_id', $assessments->pluck('id'))
            ->whereNotNull('completed_at')
            ->get();

        $responsesByStudent = $responses->groupBy('student_id');

        $response = new StreamedResponse(function () use ($students, $assessments, $responsesByStudent) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            $headers = ['student_id', 'student_name', 'student_email', 'assessment_type', 'assessment_title', 'score', 'time_spent_seconds', 'completed_at', 'grading_status'];
            fputcsv($handle, $headers);

            foreach ($students as $student) {
                $studentResponses = $responsesByStudent->get($student->id, collect());

                foreach ($assessments as $assessment) {
                    $resp = $studentResponses->firstWhere('assessment_id', $assessment->id);

                    fputcsv($handle, [
                        $student->id,
                        $student->name,
                        $student->email,
                        $assessment->assessment_type,
                        $assessment->title,
                        $resp ? $resp->score : '',
                        $resp ? $resp->time_spent : '',
                        $resp ? $resp->completed_at : '',
                        $resp ? $resp->grading_status : 'no_response',
                    ]);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="datos_curso_' . $courseId . '.csv"');

        return $response;
    }

    /**
     * Datos completos de un estudiante individual
     */
    public function studentDetail(Request $request, $courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('manage', $course);

        $student = $course->students()->where('users.id', $studentId)->firstOrFail();

        $assessments = $course->assessments()->where('is_template', false)->get();
        $responses = StudentResponse::where('student_id', $studentId)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->whereNotNull('completed_at')
            ->get();

        $likertTypes = [
            'mslq_motivation_initial', 'mslq_motivation_final',
            'mslq_strategies', 'cognitive_load',
            'course_interest', 'imms',
        ];

        $assessmentData = $assessments->map(function ($assessment) use ($responses, $likertTypes) {
            $response = $responses->firstWhere('assessment_id', $assessment->id);

            return [
                'assessment_type' => $assessment->assessment_type,
                'title' => $assessment->title,
                'completed' => $response !== null,
                'score' => $response ? $this->getEffectiveScore($response, $assessment) : null,
                'score_type' => in_array($assessment->assessment_type, $likertTypes) ? 'likert' : 'pct',
                'time_spent' => $response?->time_spent,
                'completed_at' => $response?->completed_at,
                'grading_status' => $response?->grading_status ?? 'no_response',
            ];
        });

        $profile = StudentProfile::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        $materialAccess = MaterialAccessLog::where('student_id', $studentId)
            ->whereHas('material', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->with('material:id,material_type,topic')
            ->get()
            ->map(function ($log) {
                return [
                    'material_type' => $log->material->material_type ?? null,
                    'topic' => $log->material->topic ?? null,
                    'started_at' => $log->started_at,
                    'completed_at' => $log->completed_at,
                    'time_spent_seconds' => $log->time_spent_seconds,
                ];
            });

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
            ],
            'assessments' => $assessmentData,
            'profile' => $profile?->profile_data,
            'material_access' => $materialAccess,
            'total_study_time' => $materialAccess->sum('time_spent_seconds'),
        ]);
    }

    /**
     * Obtener score efectivo de una respuesta.
     * Para evaluaciones con score directo (recall/comprensión) devuelve el score almacenado.
     * Para escalas Likert (MSLQ, CIS, CLT, IMMS) calcula el promedio de las respuestas.
     */
    private function getEffectiveScore($response, $assessment): ?float
    {
        if ($response->score !== null) {
            return round($response->score, 2);
        }

        $likertTypes = [
            'mslq_motivation_initial', 'mslq_motivation_final',
            'mslq_strategies', 'cognitive_load',
            'course_interest', 'imms',
        ];

        if (in_array($assessment->assessment_type, $likertTypes)) {
            $responses = $response->responses;
            if (is_array($responses) && count($responses) > 0) {
                $values = array_filter(array_values($responses), fn($v) => is_numeric($v));
                return count($values) > 0 ? round(array_sum($values) / count($values), 2) : null;
            }
        }

        return null;
    }
}
