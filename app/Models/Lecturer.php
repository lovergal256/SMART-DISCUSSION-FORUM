<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    protected $table = 'lecturers';
    protected $primaryKey = 'LecturerID';

    protected $fillable = [
        'UserID',
        'Department',
        'DateEmployed',
        'Status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}