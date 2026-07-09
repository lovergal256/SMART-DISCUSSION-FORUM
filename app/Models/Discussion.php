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
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
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