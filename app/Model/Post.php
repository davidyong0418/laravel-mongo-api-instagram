<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class Post extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Post';

    protected $fillable = [
        'uid','text','category'
    ];
    public function post_create($media_id, $media_path, $last_user_comment, $last_user_name)
    {
        $post = new Post();
        $post->media_id = $media_id;
        $post->media_path = $media_path;
        $post->last_user_name= $last_user_name;
        $post->last_user_comment = $last_user_comment;
        $post->save();
    }
    
}
