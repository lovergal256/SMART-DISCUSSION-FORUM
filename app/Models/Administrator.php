<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    protected $table = 'administrators';
    protected $primaryKey = 'AdministratorID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'AdministratorID',
        'UserID',
        'AccessLevel',
        'DateAssigned',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}