<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Category extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Category';
    
    protected $fillable = [
        'category','category_id'
    ];
    
}
