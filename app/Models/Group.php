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
}
