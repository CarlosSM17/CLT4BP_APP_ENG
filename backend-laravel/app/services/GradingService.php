<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\StudentResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GradingService
{
    /**
     * Obtiene las respuestas pendientes de calificación para una evaluación
     */
    public function getPendingGradingResponses(Assessment $assessment): Collection
    {
        return $assessment->responses()
            ->where('grading_status', 'pending_grading')
            ->whereNotNull('completed_at')
            ->with('student:id,name,email')
            ->get();
    }

    /**
     * Obtiene el resumen de evaluaciones pendientes de calificación para un curso
     */
    public function getCoursePendingGradingSummary(Course $course): array
    {
        // Obtener todas las evaluaciones del curso que podrían requerir calificación manual
        $assessments = $course->assessments()
            ->where('is_template', false)
            ->withCount(['responses as pending_count' => function ($q) {
                $q->where('grading_status', 'pending_grading')
                  ->whereNotNull('completed_at');
            }])
            ->get()
            ->filter(function ($assessment) {
                // Filtrar evaluaciones que requieren calificación manual o tienen preguntas abiertas
                return $assessment->requires_manual_grading || $assessment->hasOpenEndedQuestions();
            });

        $assessmentsWithPending = $assessments
            ->filter(fn($a) => $a->pending_count > 0)
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'assessment_type' => $assessment->assessment_type,
                    'pending_count' => $assessment->pending_count,
                ];
            })
            ->values();

        return [
            'total_pending' => $assessmentsWithPending->sum('pending_count'),
            'assessments' => $assessmentsWithPending->toArray(),
        ];
    }

    /**
     * Envía las calificaciones manuales para una respuesta
     */
    public function submitGrades(
        StudentResponse $response,
        array $grades,
        int $graderId
    ): StudentResponse {
        // Validar estructura de calificaciones
        $this->validateGradesStructure($grades, $response->assessment);

        // Actualizar respuesta con calificaciones manuales
        $response->update([
            'manual_scores' => $grades,
            'grading_status' => 'graded',
            'graded_by' => $graderId,
            'graded_at' => now(),
        ]);

        // Recalcular puntaje total
        $totalScore = $response->calculateTotalScore();
        $response->update(['score' => $totalScore]);

        return $response->fresh(['student', 'grader', 'assessment']);
    }

    /**
     * Valida que las calificaciones correspondan a preguntas abiertas válidas
     */
    private function validateGradesStructure(array $grades, Assessment $assessment): void
    {
        $openEndedIds = collect($assessment->questions)
            ->filter(fn($q) => in_array($q['type'] ?? '', ['text', 'essay', 'open_ended']))
            ->pluck('id')
            ->toArray();

        if (empty($openEndedIds)) {
            throw new InvalidArgumentException(
                'Esta evaluación no tiene preguntas abiertas para calificar'
            );
        }

        foreach ($grades as $grade) {
            // Validar campos requeridos
            if (!isset($grade['question_id']) || !isset($grade['score']) || !isset($grade['max_score'])) {
                throw new InvalidArgumentException(
                    'Cada calificación debe tener question_id, score y max_score'
                );
            }

            // Validar que la pregunta sea de tipo abierto
            if (!in_array($grade['question_id'], $openEndedIds)) {
                throw new InvalidArgumentException(
                    "La pregunta {$grade['question_id']} no es una pregunta abierta"
                );
            }

            // Validar rango de puntaje
            if ($grade['score'] < 0 || $grade['score'] > $grade['max_score']) {
                throw new InvalidArgumentException(
                    "Puntaje inválido para la pregunta {$grade['question_id']}: " .
                    "debe estar entre 0 y {$grade['max_score']}"
                );
            }

            // Validar max_score positivo
            if ($grade['max_score'] <= 0) {
                throw new InvalidArgumentException(
                    "El puntaje máximo debe ser mayor a 0"
                );
            }
        }
    }

    /**
     * Determina el estado de calificación cuando se envía una respuesta
     */
    public function determineGradingStatus(Assessment $assessment): string
    {
        if ($assessment->requires_manual_grading) {
            return 'pending_grading';
        }

        if ($assessment->hasOpenEndedQuestions()) {
            return 'pending_grading';
        }

        return 'auto_graded';
    }

    /**
     * Obtiene estadísticas de calificación para una evaluación
     */
    public function getGradingStats(Assessment $assessment): array
    {
        $responses = $assessment->responses()->whereNotNull('completed_at');

        return [
            'total_completed' => $responses->count(),
            'auto_graded' => (clone $responses)->where('grading_status', 'auto_graded')->count(),
            'pending_grading' => (clone $responses)->where('grading_status', 'pending_grading')->count(),
            'graded' => (clone $responses)->where('grading_status', 'graded')->count(),
            'average_score' => $responses->whereNotNull('score')->avg('score'),
        ];
    }

    /**
     * Revierte la calificación manual de una respuesta
     */
    public function revertGrading(StudentResponse $response): StudentResponse
    {
        if ($response->grading_status !== 'graded') {
            throw new InvalidArgumentException('Esta respuesta no ha sido calificada manualmente');
        }

        $response->update([
            'manual_scores' => null,
            'grading_status' => 'pending_grading',
            'graded_by' => null,
            'graded_at' => null,
            'score' => null,
        ]);

        return $response->fresh();
    }

    /**
     * Recalcula el estado de calificación de todas las respuestas de un curso
     * Útil para actualizar respuestas que se crearon antes de implementar el sistema
     */
    public function recalculateGradingStatusForCourse(Course $course): array
    {
        $updated = 0;
        $assessments = $course->assessments()->get();

        foreach ($assessments as $assessment) {
            $shouldBePending = $assessment->requires_manual_grading || $assessment->hasOpenEndedQuestions();

            if ($shouldBePending) {
                // Actualizar respuestas completadas que están como auto_graded pero deberían ser pending_grading
                $count = $assessment->responses()
                    ->whereNotNull('completed_at')
                    ->where(function ($query) {
                        $query->where('grading_status', 'auto_graded')
                              ->orWhereNull('grading_status');
                    })
                    ->whereNull('graded_at') // No han sido calificadas manualmente
                    ->update(['grading_status' => 'pending_grading']);

                $updated += $count;
            }
        }

        return [
            'updated_count' => $updated,
            'message' => "Se actualizaron {$updated} respuestas a estado pendiente de calificación",
        ];
    }
}
