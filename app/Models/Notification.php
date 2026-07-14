<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'NotificationID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'NotificationID',
        "UserID",
        'Message',
        'Type',
        'Status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}
