<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Media;
use MongoDB\BSON\ObjectID;
class SearchController extends Controller
{
    // api/search_user?pattern=&user_id=
    public function search_user(Request $request)
    {
        $search_text = $request->get('pattern');
        $user_id = $request->get('user_id');
        // 
        $type = $request->get('type');
        if($type == 'top')
        {
            $orderby = 'postDatacount';
            if($search_text == '')
            {
                $data = $this->get_top_users($user_id);
            }else{
                $search_pattern = '%'.$search_text.'%';
                $users = User::where('username', 'LIKE', $search_pattern)->where('_id', '!=', $user_id)->orderBy($orderby,'desc')->take(50)->get();
                if(!empty($users)){
                    $data = array(
                        'action'=>'true',
                        'result'=>$users
                    );
                }
                else{
                    $data = array(
                        'action'=>'false',
                        'result'=>'no result'
                    );
        }
            }
        }
        else if($type == 'people')
        {
            $orderby = 'following_count';
            if($search_text == '')
            {
                $data = $this->get_top_peoples($user_id);
            }
            else{
                $search_pattern = '%'.$search_text.'%';
                $users = User::where('username', 'LIKE', $search_pattern)->where('_id', '!=', $user_id)->orderBy($orderby,'desc')->take(50)->get();
                if(!empty($users)){
                    $data = array(
                        'action'=>'true',
                        'result'=>$users
                    );
                }
                else{
                    $data = array(
                        'action'=>'false',
                        'result'=>'no result'
                    );
                }
            }
        }
        
        return response()->json($data);
    }
    public function get_top_users($uid)
    {
        $obj_uid = new ObjectID($uid);
        $data = User::raw(function($collection) use($obj_uid)
        {
            return $collection->aggregate([
                [
                    '$match' =>[
                        '_id' =>[
                            '$ne' => $obj_uid
                        ]
                    ]
                ],
                [
                    '$sort' => [
                        'postDatacount' => -1
                ]
                ],
                [
                    '$limit' => 10
                ],
            ]);
        })->toArray();
        if(!empty($data))
        {
            $data = array(
                'action' =>true,
                'result' => $data
            );
        }
        else{
            $data = array(
                'action' =>false,
                'result' => 'false'
            );
        }
        return $data;

    }
    public function get_top_peoples($uid)
    {
        $obj_uid = new ObjectID($uid);
        $data = User::raw(function($collection) use($obj_uid)
        {
            return $collection->aggregate([
                [
                    '$match' =>[
                        '_id' =>[
                            '$ne' => $obj_uid
                        ]
                    ]
                ],
                ['$sort' => [
                    'following_count'=> -1
                ]],
                [
                    '$limit' => 10
                ],
                
            ]);
        })->toArray();

        if(!empty($data))
        {
            $data = array(
                'action' =>true,
                'result' => $data
            );
        }
        else{
            $data = array(
                'action' =>false,
                'result' => 'false'
            );
        }
        return $data;

    }
    

}
