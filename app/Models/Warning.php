<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Warning extends Model
{
    protected $table = 'warnings';
    protected $primaryKey = 'WarningID';
    public $timestamps = false;
    protected $fillable = ['UserID', 'WarningNumber', 'WarningDate'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}