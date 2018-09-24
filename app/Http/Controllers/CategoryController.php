<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Category;
class CategoryController extends Controller
{
    // api/search_user?pattern=&user_id=
    public function category_list(Request $request)
    {
        $categories = Category::all()->toArray();
        if(!empty($categories)){
            $data = array(
                'action'=>'true',
                'result'=>$categories
            );
        }
        else{
            $data = array(
                'action'=>'false',
                'result'=>'no category'
            );
        }
        return $data;
    }
    public function add_category(Request $request)
    {
        $new_category = $request->get('category');
        $check = Category::where('category',$new_category)->get()->toArray();
        if(empty($check))
        {
            $category = new Category();
            $category->category = $new_category;
            $category->save();
            $data = array(
                'action' => 'true',
                'result' =>'category added'
            );
        }
        else{
            $data = array(
                'action' => 'false',
                'result' =>'category has existed'
            );
        }
        return $data;
    }
}
