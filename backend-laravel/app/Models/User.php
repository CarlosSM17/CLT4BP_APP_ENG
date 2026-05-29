<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // Verificadores de rol
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
    public function instructedCourses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'student_id', 'course_id')
                    ->withPivot('status', 'enrollment_date', 'completion_date')
                    ->withTimestamps();
    }

    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'student_id');
    }
}
