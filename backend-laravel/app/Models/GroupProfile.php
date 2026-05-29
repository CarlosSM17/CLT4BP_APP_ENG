<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class GroupProfile extends Model
{
    protected $fillable = [
        'course_id', 'profile_data', 'student_count', 'generated_at'
    ];

    protected $casts = [
        'profile_data' => 'array',
        'generated_at' => 'datetime'
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
