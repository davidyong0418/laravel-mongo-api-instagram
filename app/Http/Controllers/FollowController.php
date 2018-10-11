<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Following;
use App\Model\Follower;
use MongoDB\BSON\ObjectID;
use App\Model\Media;
use App\Model\User;
class FollowController extends Controller
{
    // api/get_following?user_id=
    public function get_followers(Request $request)
    {
        $uid = $request->get('user_id');
        $data = Media::raw(function($collection)
        {
            return $collection->aggregate([
                [
                    '$match'=>[
                        '$expr'=>[
                            '$eq'=>[
                                '$status', 2
                            ]
                        ]
                    ]
                ],
                  [
                    '$group'    => [
                        '_id'   => '$postUserId',
                        'Object_postUserId' =>[
                            '$first' => '$object_postUserId'
                        ],
                        'count' => [
                            '$sum'  => 1
                        ]
                    ]
                ],
                [
                    '$sort' => [
                        'count' => -1
                ]
                ],
                [
                    '$limit' => 10
                ],
                [
                    '$lookup'=> [
                            'from' => 'User',
                            'localField' => 'Object_postUserId',
                            'foreignField' => '_id',
                            'as'=> 'user_info'
                        ]
                ],
                [
                    '$unwind' => '$user_info'
                ],
                [
                    '$project' => [
                        'user_id'=>'$_id',
                        'username'=>'$user_info.username',
                        'profile_pic'=>'$user_info.profile_pic_url',
                        'profile_pic_thumbnail'=>'$user_info.profile_pic_thumbnail'
                  
                    ]
                ]
            ]);
        })->toArray();
        $following_data = Following::where('uid',$uid)->get()->toArray();
        if(!empty($following_data[0]['following_users']))
        {
            foreach($data as $key=>$item)
            {
                $data[$key]['following_state'] = 'false';
                foreach($following_data[0]['following_users'] as $item_following)
                {
                    if($item['_id'] == $item_following['string_id'])
                    {
                        $data[$key]['following_state'] = 'true';
                    }
                }
            }
            
        }
        $result = array(
            'action' => 'true',
            'result' => $data
        );
        return response()->json($result);
    }
    // api/following?user_id=&following_id=
    public function following(Request $request)
    {
        $uid = $request->get('user_id');
        $following_id = $request->get('following_id');
        $following_state = $request->get('state');
        $check = Following::where('uid',$uid)->get()->toArray();
        $obj_uid = new ObjectID($uid);
        if($following_state == 1)
        {
            if(empty($check))
            {
                $new = array();
                $new[] = array('_id'=>new ObjectID($following_id),'string_id' => $following_id);
                $following = new Following;
                $following->uid = $uid;
                $following->following_users = $new;
                $following->count = 1;
                $following->save();
                User::where('_id', $uid)->update(array('following_count' => 1));
                
            }
            else{
                Following::where('uid', $uid)->push('following_users', 
                    array('_id' => new ObjectID($following_id),
                        'string_id' => $following_id
                    )
                );
                Following::where('uid', $uid)->increment('count', 1);
                User::where('_id', $uid)->increment('following_count', 1);
            }
            $data = array(
                'action'=>'true',
                'result'=>'following added'
            );
        }
        else{
            Following::raw()->findOneAndUpdate(['uid'=> $uid], ['$pull'=> ['following_users'=> ['_id'=> new ObjectID($following_id), 'string_id' => $following_id]]]);
            Following::where('uid', $uid)->increment('count', -1);
            User::where('_id', $uid)->increment('following_count', -1);
            $data = array(
                'action'=>'true',
                'result'=>'unfollowing'
            );
        }
        
        return $data;
    }
    // api/get_followers?user_id=
    public function get_following(Request $request)
    {
        $uid = $request->get('user_id');
        // $followers_data = Following::where('uid','=',$uid)->get()->toArray();
        $followers_data = Following::raw(function($collection) use($uid){
            return $collection->aggregate([
                [
                    '$match'=>[
                        'uid'=>$uid
                    ]
                ],
                [
                    '$lookup' => [
                       'from' => "User",
                       'localField' => "following_users._id",
                       'foreignField' => "_id",
                       'as' => "following_info"
                     ]
                ],
                [
                    '$addFields' => [
                        'profile_pic_url'=>'$following_info.profile_pic_url',
                        'profile_pic_thumbnail' => '$following_info.profile_pic_thumbnail',
                        'username'=>'$following_info.username'
                    ]
                ]
            ]);
        })->toArray();
        if(!empty($followers_data)){
            $data = array(
                'action'=>'true',
                'result'=> $followers_data
            );
        }
        else{
            $data = array(
                'action'=>'false',
                'result'=>'no followers'
            );
        }
        return $data;
    }

}
