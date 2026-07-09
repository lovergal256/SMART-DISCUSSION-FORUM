<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $primaryKey='TopicID';
    public $timestamps =false;

    protected $fillable=['DiscussionID','UserID','Title','Description','Status'];
    public function discussion()
    {
        return $this->belongsTo(Discussion::class,'DiscussionID','DiscussionID');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'UserID','UserID');
    }
    public function posts()
    {
        return $this->hasMany(Post::class,'TopicID','TopicID');
    }
     public function repliesCount()
{
    return $this->hasManyThrough(Reply::class, Post::class, 'TopicID', 'PostID', 'TopicID', 'PostID')->count();
}


    
    //
}
