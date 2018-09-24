<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Tag extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Text';
    
    protected $fillable = [
        'uid','text','category'
    ];
}
