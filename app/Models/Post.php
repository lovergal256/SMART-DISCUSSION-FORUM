<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
protected $primaryKey='PostID';
public $incrementing=false;
protected $keyType='string';
public $timestamps=false;

protected $fillable =['PostID','TopicID','UserID','Content','DatePosted'];

public function topic()
    {
        return $this->belongsTo(Topic::class,'TopicID','TopicID');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'UserID','UserID');
    }
    public function replies()
    {
        return $this->hasMany(Reply::class,'PostID','PostID');
    }
}
