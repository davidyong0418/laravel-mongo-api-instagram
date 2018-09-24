<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Following;
use App\Model\Follower;
use MongoDB\BSON\ObjectID;
use App\Model\Media;
use App\Model\User;
use MongoDB\BSON\UTCDateTime;
use DateTime;
class DashboardController extends Controller
{
    // api/get_following?user_id=
    public function get_posts(Request $request)
    {
        // $date =MongoDate('1536964179000');
        // return $date;
        $match = array();
        $status = $request->get('status');
        $category = $request->get('type');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $username = $request->get('username');
        
        $match = array();
        if($status != 4)
        {
            $match['$expr']['$eq'] = ['$status', (int)$status]; 
        }
        if(!empty($category) || $category != 'all')
        {
            $match['category'] = $category;
        }
        if(!empty($start_date) && !empty($end_date))
        {
            $match['created_at']['$gt'] = new UTCDateTime(new DateTime($start_date));
            $match['created_at']['$lt'] = new UTCDateTime(new DateTime($end_date));
        }
        if(!empty($email))
        {
            $search = array(
                'username' => $username
            );
            $user = User::where($search)->first();
            if (!empty($user))
            {
                $match['postUserId'] = $user['_id'];
            }
            else{
                $data  = array(
                    'action' => 'false',
                    'result' => 'User has this email is not existed'
                );
                return $data;
            }
        }

        $data = Media::raw(function($collection) use($match)
        {
            return $collection->aggregate([
                [
                    '$match' => $match
                ],
                ['$sort'  =>  [
                        'created_at' => -1
                    ]],
                [
                    '$project' => [
                        
                        'last_user_id' =>  ['$toObjectId' =>  '$last_user_id'],
                        'postUserId' =>  ['$toObjectId' =>  '$postUserId'],
                        'media_id'  =>  ['$toObjectId' =>  '$media_id'],
                        'postDataId' => '$_id',
                        'postDataUrl' => '$path',
                        'postDataType' => '$type',
                        'postDataiphonethumbnail' => '$iphone_thmubnail',
                        'postDataipadthumbnail' => '$ipad_thmubnail',
                        'postDataandroidthumbnail' => '$android_thmubnail',
                        'postDataCategory' => '$category', 
                        'status' => 1,
                        'postDataContent'  =>  1,
                        'postDataLikeCount' => 1, 
                        'postDataCommentCount' => 1, 
                        'postDataShareCount' => 1,
                        'postDataTags' => '$tags', 
                        'postDataCaption' => 1, 
                        'postDataLastCommentUserID' => 1, 
                        'postDataLastCommentUser' => 1,    
                        'postDataLastCommentText' => 1,
                        'postDatacreated_at' => '$created_at',
                        'postUserFollowState' => '0',

                       
                    ]
                ],
                [
                 '$lookup' => 
                   [
                     'from' =>  'User',
                     'localField' =>  'postUserId',
                     'foreignField' =>  '_id',
                     'as' =>  'postUserinfo'
                   ]
                ],
               [
                 '$lookup'  => 
                   [
                     'from' => 'User',
                     'localField' =>  'last_user_id',
                     'foreignField' =>  '_id',
                     'as' =>  'postDataLastCommentUser'
                   ]
                ],
                [
                    '$addFields'  => [
                        'postUserFollowingCount'  =>  '$postUserinfo.following_count'
                    ]
                ]
            ]);
        });

        return response()->json($data);
      
    }

}
