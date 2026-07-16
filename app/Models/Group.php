<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Exclusion;

class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'GroupID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
    'GroupName',
    'Description',
    'Visibility',
    'CreatedBy',
    
];

    public function members() {
    return $this->belongsToMany(User::class, 'group_members', 'GroupID', 'UserID')
                ->withPivot('Role', 'Status', 'JoinedAt')
                ->withTimestamps();
}
    public function exclusions()
{
    return $this->hasMany(Exclusion::class, 'GroupID', 'GroupID');
}

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'GroupID', 'GroupID');
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class, 'GroupID', 'GroupID');
    }
}
