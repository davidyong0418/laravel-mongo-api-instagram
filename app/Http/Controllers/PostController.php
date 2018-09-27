<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Media;
use App\Model\Post;
use App\Model\Following;
use DB;
use MongoDB\BSON\UTCDateTime;
use DateTime;
use MongoDB\BSON\ObjectID;
class PostController extends Controller
{
    public function get_all_posts(Request $request)
    {
        $init = $request->get('init');
        $uid = $request->get('user_id');
        $category = $request->get('category');
        $type= $request->get('type');
        if(!empty($init))
        {
            if($type == "all" && $category == "all")
            {
                $match='';
            }
            else{
                if($type == 'all')
                {
                    $match=[
                        'category'=>$category,
                        'status' => 2
                    ];
                }
                elseif($category == 'all')
                {
                    $match = [
                        'type'=>$type,
                        'status' => 2
                    ];
                }
                else{
                    $match = [
                        'category'=>$category,
                        'type'=>$type,
                        'status' => 2
                    ];
                }
                
            }
            if($match == '')
            {
                $data = Media::raw(function($collection) use($match,$uid)
                {
                    return $collection->aggregate([
                        ['$sort' => [
                            'created_at'=>-1
                        ]],
                        [
                            '$match' => [
                                'status' => 2
                            ]
                        ],
                        [
                            '$project'=>[
                                'last_user_id'=> ['$toObjectId'=> '$last_user_id'],
                                'postUserId'=> ['$toObjectId'=> '$postUserId'],
                                'media_id' => ['$toObjectId'=> '$media_id'],
                                'postDataId'=>'$_id',
                                'postDataUrl'=>'$path',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'postDataContent' => 1,
                                'postDataLikeCount'=>1,
                                'postDataDisLikeCount'=>1,  
                                'postDataCommentCount'=>1, 
                                'postDataShareCount'=>1,
                                'postDataTags'=>'$tags', 
                                'postDataCaption'=>1, 
                                'postDataLastCommentUserID'=>1, 
                                'postDataLastCommentUser'=>1,    
                                'postDataLastCommentText'=>1,
                                'postDatacreated_at'=>'$created_at',
                                'postDataLikeState'=>[
                                    '$filter' => [
                                                'input' => '$like_users',
                                                'as' => 'user_item',
                                                'cond' => [ '$eq'=> [ '$$user_item.uid', $uid ] ]
                                            ]
                                    ],
                                'postDataDisLikeState'=>[
                                    '$filter' => [
                                                'input' => '$dislike_users',
                                                'as' => 'dislike_user',
                                                'cond' => [ '$eq'=> [ '$$dislike_user.uid', $uid ] ]
                                            ]
                                ]
                            ]
                        ],
                        [
                         '$lookup'=>
                           [
                             'from'=> 'User',
                             'localField'=> 'postUserId',
                             'foreignField'=> '_id',
                             'as'=> 'postUserinfo'
                           ]
                        ],
                       [
                         '$lookup' =>
                           [
                             'from'=> 'User',
                             'localField'=> 'last_user_id',
                             'foreignField'=> '_id',
                             'as'=> 'postDataLastCommentUser'
                           ]
                        ],
                        [
                            '$addFields' => [
                                'postUserFollowingCount' => '$postUserinfo.following_count',
                            ]
                        ],
                        [ '$limit' => 9 ]
                    ]);
                })->toArray();
            }
            else
            {
            $data = Media::raw(function($collection) use($match,$uid)
                {
                    return $collection->aggregate([
                        ['$sort' => [
                            'created_at'=>-1
                        ]],
                        [
                            '$match'=>$match
                        ],
                        [
                            '$project'=>[
                                'last_user_id'=> ['$toObjectId'=> '$last_user_id'],
                                'postUserId'=> ['$toObjectId'=> '$postUserId'],
                                'media_id' => ['$toObjectId'=> '$media_id'],
                                'postDataId'=>'$_id',
                                'postDataUrl'=>'$path',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'postDataContent' => 1,
                                'postDataLikeCount'=>1,
                                'postDataDisLikeCount'=>1,  
                                'postDataCommentCount'=>1, 
                                'postDataShareCount'=>1,
                                'postDataTags'=>'$tags', 
                                'postDataCaption'=>1, 
                                'postDataLastCommentUserID'=>1, 
                                'postDataLastCommentUser'=>1,    
                                'postDataLastCommentText'=>1,
                                'postDatacreated_at'=>'$created_at',
                                'postDataLikeState'=>[
                                    '$filter' => [
                                                'input' => '$like_users',
                                                'as' => 'user_item',
                                                'cond' => [ '$eq'=> [ '$$user_item.uid', $uid ] ]
                                            ]
                                ],
                                'postDataDisLikeState'=>[
                                    '$filter' => [
                                                'input' => '$dislike_users',
                                                'as' => 'dislike_user',
                                                'cond' => [ '$eq'=> [ '$$dislike_user.uid', $uid ] ]
                                            ]
                                ]
                            ]
                        ],
                        [
                         '$lookup'=>
                           [
                             'from'=> 'User',
                             'localField'=> 'postUserId',
                             'foreignField'=> '_id',
                             'as'=> 'postUserinfo'
                           ]
                        ],
                       [
                         '$lookup' =>
                           [
                             'from'=> 'User',
                             'localField'=> 'last_user_id',
                             'foreignField'=> '_id',
                             'as'=> 'postDataLastCommentUser'
                           ]
                        ],
                        [
                            '$addFields' => [
                                'postUserFollowingCount' => '$postUserinfo.following_count'
                            ]
                        ],
                        [ '$limit' => 9 ]
                    ]);
                })->toArray();
            }
        }
        else{
            $after = $request->get('after');
            $after_info = Media::where('_id', $after)->get()->toArray();
            $created_at =$after_info[0]['created_at'];
            $date = new UTCDateTime(new DateTime($created_at));
            if($type == "all" && $category == "all")
            {
                $match='';

            }
            else{
                if($type == 'all')
                {
                    $match=[
                        'category'=>$category,
                        'created_at' => [
                                        '$lt' => $date
                        ],
                        'status' => 2
                    ];
                }
                elseif($category == 'all')
                {
                    $match = [
                        'type'=>$type,
                        'created_at' => [
                            '$lt' => $date
                        ],
                        'status' => 2
                    ];
                }
                else{
                    $match = [
                        'category'=>$category,
                        'type'=>$type,
                        'created_at' => [
                            '$lt' => $date
                        ],
                        'status' => 2
                    ];
                }
                
            }

            if($match == '')
            {
                $data = Media::raw(function($collection) use($match, $uid, $date)
                {
                    return $collection->aggregate([
                        ['$sort' => [
                            'created_at'=> -1
                        ]],
                        [
                            '$match'=>[ 
                                'created_at' => [
                                '$lt' => $date
                                ],
                                'status' => 2
                            ]
                        ],
                        [
                            '$project'=>[
                                'last_user_id'=> ['$toObjectId'=> '$last_user_id'],
                                'postUserId'=> ['$toObjectId'=> '$postUserId'],
                                'media_id' => ['$toObjectId'=> '$media_id'],
                                'postDataId'=>'$_id',
                                'postDataUrl'=>'$path',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'postDataContent' => 1,
                                'postDataLikeCount'=>1,
                                'postDataDisLikeCount'=>1,  
                                'postDataCommentCount'=>1, 
                                'postDataShareCount'=>1,
                                'postDataTags'=>'$tags', 
                                'postDataCaption'=>1, 
                                'postDataLastCommentUserID'=>1, 
                                'postDataLastCommentUser'=>1,    
                                'postDataLastCommentText'=>1,
                                'postDatacreated_at'=>'$created_at',
                                'postDataLikeState'=>[
                                    '$filter' => [
                                                'input' => '$like_users',
                                                'as' => 'user_item',
                                                'cond' => [ '$eq'=> [ '$$user_item.uid', $uid ] ]
                                            ]
                                ],
                                'postDataDisLikeState'=>[
                                    '$filter' => [
                                                'input' => '$dislike_users',
                                                'as' => 'dislike_user',
                                                'cond' => [ '$eq'=> [ '$$dislike_user.uid', $uid ] ]
                                            ]
                                ]
                            ]
                        ],
                        [
                         '$lookup'=>
                           [
                             'from'=> 'User',
                             'localField'=> 'postUserId',
                             'foreignField'=> '_id',
                             'as'=> 'postUserinfo'
                           ]
                        ],
                       [
                         '$lookup' =>
                           [
                             'from'=> 'User',
                             'localField'=> 'last_user_id',
                             'foreignField'=> '_id',
                             'as'=> 'postDataLastCommentUser'
                           ]
                        ],
                        [
                            '$addFields' => [
                                'postUserFollowingCount' => '$postUserinfo.following_count'
                            ]
                        ],
                        [ '$limit' => 9 ]
                    ]);
                })->toArray();
            }
            else{
                $data = Media::raw(function($collection) use($match, $uid)
                {
                    return $collection->aggregate([
                        ['$sort' => [
                            'created_at'=> -1
                        ]],
                        [
                            '$match'=>$match
                        ],
                        [
                            '$project'=>[
                                'last_user_id'=> ['$toObjectId'=> '$last_user_id'],
                                'postUserId'=> ['$toObjectId'=> '$postUserId'],
                                'media_id' => ['$toObjectId'=> '$media_id'],
                                'postDataId'=>'$_id',
                                'postDataUrl'=>'$path',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'postDataContent' => 1,
                                'postDataLikeCount'=>1,
                                'postDataDisLikeCount'=>1,  
                                'postDataCommentCount'=>1, 
                                'postDataShareCount'=>1,
                                'postDataTags'=>'$tags', 
                                'postDataCaption'=>1, 
                                'postDataLastCommentUserID'=>1, 
                                'postDataLastCommentUser'=>1,    
                                'postDataLastCommentText'=>1,
                                'postDatacreated_at'=>'$created_at',
                                'postDataLikeState'=>[
                                    '$filter' => [
                                                'input' => '$like_users',
                                                'as' => 'user_item',
                                                'cond' => [ '$eq'=> [ '$$user_item.uid', $uid ] ]
                                            ]
                                ],
                                'postDataDisLikeState'=>[
                                    '$filter' => [
                                                'input' => '$dislike_users',
                                                'as' => 'dislike_user',
                                                'cond' => [ '$eq'=> [ '$$dislike_user.uid', $uid ] ]
                                            ]
                                ]
                            ]
                        ],
                        [
                         '$lookup'=>
                           [
                             'from'=> 'User',
                             'localField'=> 'postUserId',
                             'foreignField'=> '_id',
                             'as'=> 'postUserinfo'
                           ]
                        ],
                       [
                         '$lookup' =>
                           [
                             'from'=> 'User',
                             'localField'=> 'last_user_id',
                             'foreignField'=> '_id',
                             'as'=> 'postDataLastCommentUser'
                           ]
                        ],
                        [
                            '$addFields' => [
                                'postUserFollowingCount' => '$postUserinfo.following_count'
                            ]
                        ],
                        [ '$limit' => 9 ]
                    ]);
                })->toArray();
            }
            
           
        }
        $send_info = array();
        $following_info = Following::where('uid',$uid)->get()->toArray();

        foreach ($data as $key => $item)
        {
             $data[$key]['postUserFollowState'] = false;
            if(!empty($following_info))
            {
                foreach($following_info[0]['following_users'] as $following_user)
                {
                    if($item['postUserId'] == $following_user['string_id'])
                    {
                        $data[$key]['postUserFollowState'] = true;
                    }
                }
            }
            
        }
        $send_info['data']['children'] = $data;
        if(!empty($data))
        {

            if(count($data) == 9)
            {
                $send_info['data']['after'] = $data[8]['_id'];
            	$send_info['action'] = 'true';

            }
            else{
                $send_info['data']['after'] = 'end';
            	$send_info['action'] = 'true';

            }
            
        }
        else{
            $send_info['action'] = 'true';
            $send_info['data']['after']  = 'end';
        }
        
        return response()->json($send_info);
    }
    public function get_per_page_posts(Request $request)
    {
        $before = $this->request('before');
        $after = $this->request('after');
    }
    public function post_create($media_id, $media_path, $last_user_comment, $last_user_name)
    {
        $post = new Post();
        $post->media_id = $media_id;
        $post->media_path = $media_path;
        $post->last_user_name= $last_user_name;
        $post->last_user_comment = $last_user_comment;
        $post->save();
    }
    public function get_post_data(Request $request)
    {
        $media_id = $request->get('postDataId');
        $uid = $request->get('user_id');
        $obj_media_id = new ObjectID($media_id);
        $data = Media::raw(function($collection) use($uid,$obj_media_id)
                {
                    return $collection->aggregate([
                        [
                            '$match' => [
                                '_id' => $obj_media_id,
                                'status'=> 2,
                            ]
                        ],
                        [
                            '$project'=>[
                                'last_user_id'=> ['$toObjectId'=> '$last_user_id'],
                                'postUserId'=> ['$toObjectId'=> '$postUserId'],
                                'media_id' => ['$toObjectId'=> '$media_id'],
                                'postDataId'=>'$_id',
                                'postDataUrl'=>'$path',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'postDataContent' => 1,
                                'postDataLikeCount'=>1, 
                                'postDataDisLikeCount'=>1, 
                                'postDataCommentCount'=>1, 
                                'postDataShareCount'=>1,
                                'postDataTags'=>'$tags', 
                                'postDataCaption'=>1, 
                                'postDataLastCommentUserID'=>1, 
                                'postDataLastCommentUser'=>1,    
                                'postDataLastCommentText'=>1,
                                'postDatacreated_at'=>'$created_at',
                                'status'=>1,
                                'postDataLikeState'=>[
                                    '$filter' => [
                                                'input' => '$like_users',
                                                'as' => 'user_item',
                                                'cond' => [ '$eq'=> [ '$$user_item.uid', $uid ] ]
                                            ]
                                ],
                                'postDataDisLikeState'=>[
                                    '$filter' => [
                                                'input' => '$dislike_users',
                                                'as' => 'dislike_user',
                                                'cond' => [ '$eq'=> [ '$$dislike_user.uid', $uid ] ]
                                            ]
                                ]
                            ]
                        ],
                        [
                         '$lookup'=>
                           [
                             'from'=> 'User',
                             'localField'=> 'postUserId',
                             'foreignField'=> '_id',
                             'as'=> 'postUserinfo'
                           ]
                        ],
                       [
                         '$lookup' =>
                           [
                             'from'=> 'User',
                             'localField'=> 'last_user_id',
                             'foreignField'=> '_id',
                             'as'=> 'postDataLastCommentUser'
                           ]
                        ],
                        [
                            '$addFields' => [
                                'postUserFollowingCount' => '$postUserinfo.following_count'
                            ]
                        ]
                    ]);
                })->toArray();
        if(!empty($data))
        {
            $result = array(
                'action' => 'true',
                'result' => $data
            );
        }
        else
        {
            $result = array(
                'action' => 'false',
                'result' => 'No data'
            );
        }
        return response()->json($result);
    }
    
}
