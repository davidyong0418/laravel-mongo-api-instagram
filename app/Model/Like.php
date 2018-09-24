<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Like extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Like';
    
    protected $fillable = [
        'uid','media_id','like_state'
    ];
}
