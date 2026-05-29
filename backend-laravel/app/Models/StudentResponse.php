<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'responses',
        'score',
        'time_spent',
        'started_at',
        'completed_at',
        'grading_status',
        'manual_scores',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'responses' => 'array',
        'score' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'manual_scores' => 'array',
        'graded_at' => 'datetime',
    ];

    // Relaciones
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeByAssessment($query, $assessmentId)
    {
        return $query->where('assessment_id', $assessmentId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopePendingGrading($query)
    {
        return $query->where('grading_status', 'pending_grading');
    }

    public function scopeGraded($query)
    {
        return $query->where('grading_status', 'graded');
    }

    public function scopeAutoGraded($query)
    {
        return $query->where('grading_status', 'auto_graded');
    }

    // Métodos auxiliares
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Calcula el puntaje automático (solo preguntas con respuesta correcta).
     * Retorna null si la evaluación no tiene preguntas calificables (ej. escala Likert/MSLQ).
     */
    public function calculateScore(): ?float
    {
        if (empty($this->responses) || empty($this->assessment->questions)) {
            return null;
        }

        $scorableQuestions = collect($this->assessment->questions)
            ->filter(fn($q) => isset($q['correct_answer']));

        if ($scorableQuestions->isEmpty()) {
            return null;
        }

        $totalQuestions = $scorableQuestions->count();
        $correctAnswers = 0;

        foreach ($this->responses as $questionId => $answer) {
            $question = $scorableQuestions->firstWhere('id', $questionId);

            if ($question && $answer === $question['correct_answer']) {
                $correctAnswers++;
            }
        }

        return ($correctAnswers / $totalQuestions) * 100;
    }

    /**
     * Calcula el puntaje total incluyendo calificaciones manuales
     */
    public function calculateTotalScore(): float
    {
        if (empty($this->assessment->questions)) {
            return 0;
        }

        $questions = collect($this->assessment->questions);
        $totalQuestions = $questions->count();

        if ($totalQuestions === 0) {
            return 0;
        }

        // Identificar preguntas automáticas vs manuales
        $autoQuestions = $questions->filter(fn($q) => !in_array($q['type'] ?? '', ['text', 'essay', 'open_ended']));
        $manualQuestions = $questions->filter(fn($q) => in_array($q['type'] ?? '', ['text', 'essay', 'open_ended']));

        $autoScore = 0;
        $manualScore = 0;

        // Calcular puntaje automático
        if ($autoQuestions->isNotEmpty()) {
            $autoCorrect = 0;
            foreach ($this->responses ?? [] as $questionId => $answer) {
                $question = $autoQuestions->firstWhere('id', $questionId);
                if ($question && isset($question['correct_answer']) && $answer === $question['correct_answer']) {
                    $autoCorrect++;
                }
            }
            $autoScore = $autoQuestions->count() > 0
                ? ($autoCorrect / $autoQuestions->count()) * 100
                : 0;
        }

        // Calcular puntaje manual
        if ($manualQuestions->isNotEmpty() && !empty($this->manual_scores)) {
            $manualTotal = 0;
            $manualMax = 0;
            foreach ($this->manual_scores as $grade) {
                $manualTotal += $grade['score'] ?? 0;
                $manualMax += $grade['max_score'] ?? 0;
            }
            $manualScore = $manualMax > 0 ? ($manualTotal / $manualMax) * 100 : 0;
        }

        // Ponderar según cantidad de preguntas de cada tipo
        $autoWeight = $autoQuestions->count() / $totalQuestions;
        $manualWeight = $manualQuestions->count() / $totalQuestions;

        return ($autoScore * $autoWeight) + ($manualScore * $manualWeight);
    }

    /**
     * Verifica si requiere calificación manual
     */
    public function requiresManualGrading(): bool
    {
        return $this->grading_status === 'pending_grading';
    }

    /**
     * Verifica si ya fue calificado manualmente
     */
    public function isManuallyGraded(): bool
    {
        return $this->grading_status === 'graded' && !empty($this->manual_scores);
    }
}
