<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'enrollment_date',
        'completion_date',
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'completion_date' => 'datetime',
    ];

    // Relaciones
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Scopes
    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
