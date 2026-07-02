<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
protected $primaryKey='ReplyID';
public $incrementing=false;
protected $keyType='string';
public $timestamps=false;

protected $fillable =['PostID','UserID','Body','DateCreated','ReplyID'];

public function post()
    {
        return $this->belongsTo(Post::class,'PostID','PostID');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'UserID','UserID');
    }
}
