<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
 
class Media extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Media';
    
    protected $fillable = [
        'uid','type','path','tag','category'
    ];
    public function update_post($media_id, $type, $value1 = NULL,$value2 = NULL)
    {
        switch ($type) {
            case 'comment':
                Media::where('_id', $media_id)->increment('postDataCommentCount', 1, ['postDataLastCommentText' => $value1], ['postDataLastCommentUserID' => $value2]);
                break;

            case 'like':
                Media::where('_id', $media_id)->increment('postDataLikeCount', 1);
                break;

            case 'unlike':
                Media::where('_id', $media_id)->increment('postDataLikeCount', -1);
                break;
            
        }
        $data='success';
        return $data;
    }
}
