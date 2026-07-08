<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizScore extends Model
{
    protected $table = 'quiz_scores';

    protected $primaryKey = 'QuizScoreID';

    protected $fillable = [
        'UserID',
        'QuizID',
        'Score',
        'DateRecorded',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'QuizID', 'QuizID');
    }
}
