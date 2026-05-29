<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialAccessLog extends Model
{
    protected $fillable = [
        'material_id',
        'student_id',
        'started_at',
        'completed_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(InstructionalMaterial::class, 'material_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
