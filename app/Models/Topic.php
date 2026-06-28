<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $primaryKey='TopicID';
    public $timestamps =false;

    protected $fillable=['GroupID','UserID','Title','Description','Status'];
    public function group()
    {
        return $this->belongsTo(Group::class,'GroupID','GroupID');
    }
    public function user()
    {
        return $this->belongsTo(User::User,'UserID','UserID');
    }
    public function posts()
    {
        return $this->hasMany(Post::class,'TopicID','TopicID');
    }


    
    //
}
