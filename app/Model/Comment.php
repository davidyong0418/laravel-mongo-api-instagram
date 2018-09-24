<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Comment extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Comment';
    
    protected $fillable = [
        'uid','comment','media_id'
    ];
    
}
