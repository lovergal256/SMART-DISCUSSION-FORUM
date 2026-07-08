<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = [
        'quiz_id',
        'type',
        'question_text',
        'points',
        'answer_pattern',
        'order',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class, 'question_id');
    }
}
