<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Blacklist extends Model
{
    protected $table = 'blacklists';
    protected $primaryKey = 'BlacklistID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['BlacklistID', 'UserID', 'Reason', 'StartDate', 'EndDate'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}