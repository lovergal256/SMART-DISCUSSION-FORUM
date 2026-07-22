<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
     use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'UserID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'FullName',
        'Email',
        'Password',
        'RoleID',
        'DateJoined',
        'LastActiveDate',
        'Theme',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    public function getAuthIdentifierName()
    {
        return 'UserID';
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'UserID', 'UserID');
    }
    public function roleRelation()
{
    return $this->belongsTo(Role::class, 'RoleID', 'RoleID');
}

public function getRoleNameAttribute()
{
    return strtolower((string) ($this->roleRelation->RoleName ?? ''));
}

public function warnings()
{
    return $this->hasMany(Warning::class, 'UserID', 'UserID');
}

public function blacklists()
{
    return $this->hasMany(Blacklist::class, 'UserID', 'UserID');
}

public function groups()
{
    return $this->belongsToMany(Group::class, 'group_members', 'UserID', 'GroupID')
                ->wherePivot('Status', 'approved')
                ->withPivot('Role', 'Status', 'JoinedAt')
                ->withTimestamps();
}
}