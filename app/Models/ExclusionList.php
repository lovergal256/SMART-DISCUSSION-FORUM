<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExclusionList extends Model
{
    protected $table = 'exclusion_lists';
    protected $primaryKey = 'ExclusionID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'UserID',
        'ExcludedUserID',
        'ContentType',
        'ContentID',
        'ExclusionDate',
    ];

    public function excludedUser()
    {
        return $this->belongsTo(User::class, 'ExcludedUserID', 'UserID');
    }

    public function excludingUser()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}