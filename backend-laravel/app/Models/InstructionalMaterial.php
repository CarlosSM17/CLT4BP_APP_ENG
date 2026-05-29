<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructionalMaterial extends Model
{
    protected $fillable = [
        'course_id',
        'material_type',
        'target_type',
        'target_student_id',
        'content',
        'is_active',
        'timer_seconds',
        'activated_at',
        'deactivated_at'
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function targetStudent()
    {
        return $this->belongsTo(User::class, 'target_student_id');
    }

    public function accessLogs()
    {
        return $this->hasMany(MaterialAccessLog::class, 'material_id');
    }
}
