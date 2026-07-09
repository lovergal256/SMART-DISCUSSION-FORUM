<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'QuizID', 'QuizID');
    }
}