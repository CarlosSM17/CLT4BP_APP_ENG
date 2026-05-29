<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'student_id', 'course_id', 'profile_data', 'generated_at'
    ];

    protected $casts = [
        'profile_data' => 'array',
        'generated_at' => 'datetime'
    ];

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
