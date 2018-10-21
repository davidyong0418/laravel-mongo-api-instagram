<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Comment;
use App\Model\Media;
use App\Model\User;
use App\Model\Tag;
use DB;
use App\Model\Post;
use Illuminate\Database\Eloquent\Collection;
use App\Model\CommentId;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;
use DateTime;
class CommentController extends Controller
{
    // api/get_following?user_id=
    public function get_post_comment(Request $request)
    {
        $media_id = $request->get('media_id');
        $media_object_id = new ObjectID($media_id);
        $comments = Media::raw(function($collection) use($media_object_id, $media_id)
        {
            return $collection->aggregate([
                [
                    '$match'=>[
                        '_id' => $media_object_id
                    ]
                ],
                [
                    '$project'=>[
                        'postUserId'=>0,
                        'follow_count'=>0,
                        'type'=>0,
                        'path'=>0,
                        'thumbnail'=>0,
                        'category'=>0,
                        'postDataCaption'=>0,
                        'postDataLastCommentUserID'=>0,
                        'postDataShareCount'=>0,
                        'postDataCommentCount'=>0,
                        'postDataLikeCount'=>0,
                        'postDataLastCommentText'=>0,
                        'like_users'=>0,
                        'tags'=>0,
                        'updated_at'=>0,
                        '_id' =>0
                    ]
                ],
                [
                    '$unwind' =>'$comments'
                ],
                [
                    '$project'=>[
                        'user_id'=> ['$toObjectId'=> '$comments.uid'],
                        'comment'=>'$comments.comment'
                    ]
                ],
                [
                    '$lookup' =>[
                        'from' => 'User',
                        'localField' => 'user_id',
                        'foreignField' => '_id',
                        'as' => 'commentsdata'
                    ]
                ],
                [
                    '$unwind' =>'$commentsdata'
                ],
                [
                    '$addFields'=>[
                        'username'=> '$commentsdata.username',
                        'profile_pic_url'=>'$commentsdata.profile_pic_url',
                    ]
                ]
                
            ]);
        })->toArray();
        if (empty($comments))
        {
            $data = array(
                'action' => 'false',
                'result' => 'No comment'
            );
        }
        else{
            $data = array(
                'action' => 'true',
                'result' => $comments
            );
        }
        return response()->json($data);
    }
    public function add_post_comment(Request $request)
    {
        
        $uid = $request->get('user_id');
        $media_id = $request->get('media_id');
        $comment = $request->get('comment');
        $today = date('Y-m-d h:i:s');
        $check = Media::where('_id', $media_id)->get()->toArray();
        $dd = empty($check[0]['tags']);
        if (substr($comment, 0, 1) == '#')
        {
            $check_tag = Tag::where('tag', $comment)->get()->toArray();
            if(empty($check[0]['tags']))
            {
                
                Media::where('_id', $media_id)->push('tags', array(
                    'uid' => new ObjectID($uid),
                    'tag' => $comment,
                    'tag_date' => $today
                ));
            }
            if (empty($check_tag))
            {
                $tag = new Tag();
                $tag->user_id = $uid;
                $tag->tag = $tag;
                $tag->save();
            }
        }
        
        if(!empty($check))
        {
            $update_info = array(
                'postDataLastCommentUserID' => $uid,
                'postDataLastCommentText' => $comment
            );
            $new_comment = Media::where('_id', $media_id)->push('comments', array(
                'uid' => new ObjectID($uid),
                'comment' => $comment,
                'comment_date' => $today));
            $postdatalikecomment_date = new UTCDateTime(new DateTime($today));
            Media::where('_id', $media_id)->push('postdatalikecomment', array(
                    'uid' => $uid,
                    'object_uid'=>new ObjectID($uid) ,
                    'type' => 'comment',
                    'created_at'=> $postdatalikecomment_date,
                    'comment'=>$comment
                ));
            Media::where('_id', $media_id)->update($update_info,['upsert' => true]);
            $media = new Media();
            $media->update_post($media_id, 'comment', $comment, $uid);
            $comment_count = Media::where('_id', $media_id)->get()->toArray();
            $data = array(
                'action'=>'true',
                'result'=>$comment_count[0]['postDataCommentCount']
            );
            return response()->json($data);
        }
        else
        {
            $data= array(
                'action'=> 'false',
                'result'=>'no media'
            );
            return response()->json($data);
        }

    }
    public function generate_id( $length = 18 ) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $id  = substr( str_shuffle( $chars ), 0, $length );
        return $id;
    }
    public function get_own_post(Request $request)
    {
        $uid = $request->get('user_id');
        $own_post = Media::where('uid', $uid)->get()->toArray();
        if (!empty($own_post)){
            $data = array(
                'action' => 'true',
                'result' => $own_post
            );
        }
        else{
            $data = array(
                'action' => 'false',
                'result' => "you didn't post"
            );
        }
        return $data;
    }

}
