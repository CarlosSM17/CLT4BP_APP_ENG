<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'description',
        'learning_objectives',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'learning_objectives' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relaciones
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'student_id')
                    ->withPivot('status', 'enrollment_date', 'completion_date')
                    ->withTimestamps();
    }

    // Relación con evaluaciones
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', '%' . $search . '%')
                     ->orWhere('description', 'like', '%' . $search . '%');
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canEnroll(): bool
    {
        return $this->status === 'active';
    }

    public function enrolledStudentsCount(): int
    {
        return $this->enrollments()->where('status', 'enrolled')->count();
    }
}
