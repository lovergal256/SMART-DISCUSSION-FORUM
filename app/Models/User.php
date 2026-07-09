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

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function getEmailForVerification()
    {
        return $this->Email;
    }

    protected function casts(): array
    {
        return [
            'DateJoined' => 'datetime',
            'LastActiveDate' => 'datetime',
        ];
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'UserID', 'GroupID')
                    ->withPivot('Role', 'JoinedAt');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleID', 'RoleID');
    }
}