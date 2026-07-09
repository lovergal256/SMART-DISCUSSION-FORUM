<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'UserID';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'FullName', 'Email', 'Password', 'DateJoined',
        'LastActiveDate', 'RoleID',
    ];

    protected $hidden = ['Password'];

    public function getAuthPassword(): string
    {
        return $this->Password;
    }
}