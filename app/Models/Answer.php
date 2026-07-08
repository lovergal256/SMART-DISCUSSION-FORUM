<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $table = 'quiz_answers';

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'choice_id',
        'answer_text',
        'is_correct',
        'points_awarded',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class, 'choice_id');
    }
}
