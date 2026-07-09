<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $table = 'answers';
    protected $primaryKey = 'AnswerID';
    public $timestamps = false;

    protected $fillable = [
        'AttemptID',
        'QuestionID',
        'SelectedOption',
        'IsCorrect',
        'MarksAwarded',
        'DateAnswered',
    ];

    public function attempt()
    {
        return $this->belongsTo(Attempt::class, 'AttemptID', 'AttemptID');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'QuestionID', 'QuestionID');
    }
}