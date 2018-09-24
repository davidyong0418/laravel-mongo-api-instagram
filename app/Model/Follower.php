<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Follower extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Follower';
    
    protected $fillable = [
        'uid','follower_id','rejectordenyoption'
    ];
    
}
