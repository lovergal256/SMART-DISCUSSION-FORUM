<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $fillable = [
        'lecturer_id',
        'title',
        'description',
        'available_from',
        'available_until',
        'duration_minutes',
        'is_published',
        'results_released',
    ];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'quiz_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class, 'quiz_id');
    }
}
