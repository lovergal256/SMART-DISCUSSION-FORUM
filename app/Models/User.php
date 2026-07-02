<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticable
{
    use Notifiable;
    protected $primaryKey ='UserID';
    public $incrementing ='false';
    protected $keyType ='string';
    public $timestamps ='false';

    protected $fillable=['UserID','FullName','Email','Password','DateJoined','LastActiveDate','RoleID']
    ;
    protected $hidden =['Password'];

    public function getAuthPassword()
    {
        return $this->Password;
    }
        public function role()
    {
        return $this->belongsTo(Role::class,'RoleID','RoleID');
    }
        public function topics()
    {
        return $this->hasMany(Topic::class,'UserID','UserID');
    }
    public function posts()
    {
        return $this->hasMany(Post::class,'UserID','UserID');
    }
    public function warnings()
    {
        return $this->hasMany(Warning::class,'UserID','UserID');
    }
    
}








