<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    protected $primaryKey = 'DiscussionID';

    protected $fillable = [
        'Title',
        'Description',
        'UserID',
        'GroupID',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
    public function group()
{
    return $this->belongsTo(\App\Models\Group::class, 'GroupID', 'GroupID');
}
    public function topics()
    {
        return $this->hasMany(Topic::class, 'DiscussionID', 'DiscussionID');
    }
    public function getRouteKeyName()
{
    return 'DiscussionID';
}
}