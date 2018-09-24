<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Comment;
use App\Model\Media;
use App\Model\User;
use App\Model\Tag;
use DB;
use Illuminate\Database\Eloquent\Collection;
class TagController extends Controller
{
    // api/get_following?user_id=
    public function get_post_by_tag(Request $request)
    {
        $tag = $request->get('tag');
        $tags = Media::where('tag',$tag)->get()->toArray();
        if(!empty($tags))
        {
            $data = array(
                'action' => 'ture',
                'result' => $tags
            );
        }
        else
        {
            $data = array(
                'action' => 'false',
                'result' => 'no tag'

            );
        }
        return $data;
    }
    public function get_tags(Request $request)
    {
        // $tag = $request->get('tag');
        $uid = $request->get('user_id');
        $tags = Tag::where('_id',$uid)->get()->toArray();
        if(!empty($tags))
        {
            $data = array(
                'action' => 'true',
                'result' => $tags
            );
        }
        else{
            $data = array(
                'action' => 'false',
                'result' => 'no tag'
            );
        }
        return $data;
    }

}
