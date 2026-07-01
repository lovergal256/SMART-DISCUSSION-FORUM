<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'UserID';

    protected $fillable = [
        'FullName', 'Email', 'Password', 'DateJoined',
        'LastActiveDate', 'RoleID',
    ];

    protected $hidden = ['Password'];

    public function getAuthPassword(): string
    {
        return 'Password';
    }
}