<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Text extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Text';
    
    protected $fillable = [
        'uid','text','category'
    ];
}
