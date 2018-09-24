<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Auth test
Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
// api
/* User Controller*/
Route::get('signup','UserController@register');
Route::get('signup_page','UserController@signup_page');
Route::get('signin','UserController@signin');
// api/reset_password?user_id=&current_password=&new_password=
Route::post('reset_password','UserController@reset_password');
 // api/send_new_password?user_id=&new_password=
Route::post('forgot_password','UserController@forgot_password');
// api/profile_edit?user_id=&email=&bio=&username=
Route::post('profile_edit','UserController@profile_edit');
// api/all_users?user_id=
Route::get('all_users','UserController@all_users');
//  api/change_profile_pic?user_id=&type=&upload_file=
Route::post('change_profile_pic','UploadController@change_profile_pic');
Route::post('get_top_users','SearchController@get_top_users');
/* Search Controller*/
// api/search_user?pattern=&user_id=
Route::post('search_user','SearchController@search_user');
/* Category Controller*/
// api/category_list
Route::post('category_list','CategoryController@category_list');
Route::post('add_category','CategoryController@add_category');
// 2018-08-09
// api/following?user_id=""&following_id=
Route::post('following','FollowController@following');
// api/get_following?user_id=
 // api/get_followers?user_id=
Route::post('get_followers','FollowController@get_followers');
 // api/upload_media?user_id=&type=&upload_file
// api/upload_media?user_id=
Route::post('upload_media','UploadController@upload_media');
// api/upload_text?user_id=
Route::post('upload_text','UploadController@upload_text');
// api/user_posts?user_id=
Route::post('own_get_posts','UserController@get_posts');
 // api/user_like?user_id=&media_id=&like_state=
Route::post('user_like','UserController@user_like');
 // api/user_dislike?user_id=&media_id=&dislike_state=
Route::post('user_dislike','UserController@user_dislike');
// api/facebook_login?user_id=&email=&password=
Route::get('facebook_login','UserController@facebook_login');
// api/top_users?user_id=
Route::post('register_confirm','UserController@register_confirm');
Route::post('get_userinfo','UserController@get_userinfo');

Route::post('get_userinfo_postdata', 'UserController@get_userinfo_postdata');
Route::post('get_own_post','CommentController@get_own_post');
Route::post('get_post_comment', 'CommentController@get_post_comment');
Route::post('add_post_comment','CommentController@add_post_comment');
Route::post('get_post_by_tag','TagController@get_post_by_tag');
Route::post('get_tags','TagController@get_tags');
// 09-02
Route::get('get_all_posts','PostController@get_all_posts');
Route::get('get_userinfo_and_top_postdata','UserController@get_userinfo_and_top_postdata');
Route::get('get_userinfo_and_recent_postdata','UserController@get_userinfo_and_recent_postdata');

Route::post('get_post_data', 'PostController@get_post_data');

Route::post('get_top_peoples', 'SearchController@get_top_peoples');


 Route::post('send-sms','SmsController@sendMessage');



 Route::get('get_like_posts','UserController@get_like_posts');
 Route::post('get_feed_posts','UserController@get_feed_posts');
 Route::post('token_auth','UserController@token_auth');