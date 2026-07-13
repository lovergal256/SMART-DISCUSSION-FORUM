<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

public function lecturer()
{
    return $this->hasOne(Lecturer::class, 'UserID', 'UserID');
}
}
