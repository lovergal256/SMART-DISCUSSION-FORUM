<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';
    protected $primaryKey = 'QuestionID';
    public $timestamps = false;

    protected $fillable = [
        'QuizID',
        'QuestionText',
        'OptionA',
        'OptionB',
        'OptionC',
        'OptionD',
        'CorrectOption',
        'Marks',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'QuizID', 'QuizID');
    }
}