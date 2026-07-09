<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exclusion extends Model
{
    protected $table = 'exclusions';

    protected $fillable = [
        'UserID',
        'ExcludedUserID',
        'GroupID',
    ];

    public function excludedUser() {
        return $this->belongsTo(User::class, 'ExcludedUserID', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'UserID', 'id');
    }

    public function group() {
        return $this->belongsTo(Group::class, 'GroupID', 'GroupID');
    }
}
