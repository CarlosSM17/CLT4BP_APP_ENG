<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CltEffectsSelection extends Model
{
    protected $table = 'clt_effects_selection';

    protected $fillable = [
        'course_id',
        'selected_effects',
        'notes'
    ];

    protected $casts = [
        'selected_effects' => 'array'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
