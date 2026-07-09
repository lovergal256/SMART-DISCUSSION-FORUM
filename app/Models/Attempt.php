<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $table = 'attempts';
    protected $primaryKey = 'AttemptID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'QuizID',
        'StartTime',
        'EndTime',
        'Status',
        'Score',
        'AttemptDate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'QuizID', 'QuizID');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'AttemptID', 'AttemptID');
    }
}