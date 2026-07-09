<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';
    protected $primaryKey = 'QuizID';

    protected $fillable = [
        'Title',
        'StartTime',
        'Duration',
        'GroupID',
        'LecturerID',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'GroupID', 'GroupID');
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'LecturerID', 'UserID');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'QuizID', 'QuizID');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'QuizID', 'QuizID');
    }
}