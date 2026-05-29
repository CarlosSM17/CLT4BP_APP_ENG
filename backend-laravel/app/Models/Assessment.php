<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'assessment_type',
        'title',
        'description',
        'questions',
        'config',
        'is_active',
        'time_limit',
        'is_template',
        'requires_manual_grading',
        'source_template_id',
    ];

    protected $casts = [
        'questions' => 'array',
        'config' => 'array',
        'is_active' => 'boolean',
        'is_template' => 'boolean',
        'requires_manual_grading' => 'boolean',
    ];

    // Relaciones
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function responses()
    {
        return $this->hasMany(StudentResponse::class);
    }

    public function studentResponses()
    {
        return $this->hasMany(StudentResponse::class);
    }

    public function sourceTemplate()
    {
        return $this->belongsTo(Assessment::class, 'source_template_id');
    }

    public function derivedAssessments()
    {
        return $this->hasMany(Assessment::class, 'source_template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('assessment_type', $type);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeNotTemplates($query)
    {
        return $query->where('is_template', false);
    }

    public function scopeWithPendingGrading($query)
    {
        return $query->whereHas('responses', function ($q) {
            $q->where('grading_status', 'pending_grading')
              ->whereNotNull('completed_at');
        });
    }

    // Métodos auxiliares
    public function isCompleted($studentId): bool
    {
        return $this->responses()
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function getResponse($studentId)
    {
        return $this->responses()
            ->where('student_id', $studentId)
            ->first();
    }

    public function completionRate(): float
    {
        $totalStudents = $this->course?->enrolledStudentsCount() ?? 0;
        if ($totalStudents === 0) return 0;

        $completed = $this->responses()
            ->whereNotNull('completed_at')
            ->count();

        return ($completed / $totalStudents) * 100;
    }

    /**
     * Verifica si la evaluación tiene preguntas abiertas/texto
     */
    public function hasOpenEndedQuestions(): bool
    {
        if (empty($this->questions)) {
            return false;
        }

        return collect($this->questions)->contains(function ($question) {
            return in_array($question['type'] ?? '', ['text', 'essay', 'open_ended']);
        });
    }

    /**
     * Obtiene las preguntas abiertas de la evaluación
     */
    public function getOpenEndedQuestions(): array
    {
        if (empty($this->questions)) {
            return [];
        }

        return collect($this->questions)
            ->filter(fn($q) => in_array($q['type'] ?? '', ['text', 'essay', 'open_ended']))
            ->values()
            ->toArray();
    }

    /**
     * Crea una evaluación desde una plantilla
     */
    public static function createFromTemplate(int $templateId, int $courseId, array $overrides = []): self
    {
        $template = self::where('id', $templateId)
            ->where('is_template', true)
            ->firstOrFail();

        return self::create([
            'course_id' => $courseId,
            'assessment_type' => $template->assessment_type,
            'title' => $overrides['title'] ?? $template->title,
            'description' => $overrides['description'] ?? $template->description,
            'questions' => $template->questions,
            'config' => array_merge($template->config ?? [], $overrides['config'] ?? []),
            'is_active' => false,
            'is_template' => false,
            'source_template_id' => $template->id,
            'time_limit' => $overrides['time_limit'] ?? $template->time_limit,
            'requires_manual_grading' => $template->requires_manual_grading || $template->hasOpenEndedQuestions(),
        ]);
    }
}
