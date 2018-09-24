<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Following extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Following';
    // user id, following user id, true/ false. 
    protected $fillable = [
        'uid','following_id','rejectordenyoption'
    ];
   
}
