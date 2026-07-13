<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $table = 'lecturers';

    protected $primaryKey = 'LecturerID';

    public $incrementing = true;

    protected $keyType = 'int';

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
