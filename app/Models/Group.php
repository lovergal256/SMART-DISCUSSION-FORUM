<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'GroupID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $guarded = [
        'GroupName',
        'Description'
    ];
}
