<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Following;
use App\Model\Media;
use App\Model\Like;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Mail;
use DB;
use SimpleEmailServiceMessage;
use SimpleEmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Model\Post;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;
use DateTime;
use Twilio;
use Twilio\Rest\Client;
use Crypt;
class UserController extends Controller
{
    public function __construct()
    {
        
    }
    //
    public function index()
    {
        $users=User::all();
        return view('user/userindex',compact('users'));
    }

    
    public function register(Request $request)
    { 
        $first_name = $request->get('first_name');
        $last_name = $request->get('last_name');
        $username = $request->get('username');
        $password = $request->get('password');
        $phone_number = $request->get('phone_number');
        $email = $request->get('email');       
        $type = $request->get('type');

        if($type == 'email')
        {
            $check_username = User::where('username', '=', $username)->get()->toArray();
            $check_email = User::where('email', '=', $email)->get()->toArray();
            if(!empty($check_username) || !empty($check_email)){
                if(!empty($check_username)){
                    $data = array(
                        'action' =>'false',
                        'result' => 'username existed'
                    );
                    return $data;
                }
                if(!empty($check_email)){
                    $data = array(
                        'action' =>'false',
                        'result' => 'email existed'
                    );
                    return $data;
                }
            }
            else{
                $input = $request->all();
                $_token = str_random(25);
                $digits = 4;
                
                $data_info['confirmation'] = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
                $data_info['email'] = $email;
                if($this->valid_email($email) != true)
                {
                    $data = array(
                        'action'=> 'false',
                        'result'=> 'invalid email'
                    );
                    return $data;
                }
                
                $data_info['first_name'] = $first_name;
                $data_info['last_name'] = $last_name;
                $user = new User();
                $expire_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + 3 minutes'));
                $user->expire_date = $expire_date;
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->username = $username;
                $user->email = $email;       
                $user->password = $password;
                $user->profile_pic_url = 'https://www.sparklabs.com/forum/styles/comboot/theme/images/default_avatar.jpg';
                $user->profile_pic_thumbnail = 'https://www.sparklabs.com/forum/styles/comboot/theme/images/default_avatar.jpg';
                $user->_token = $_token;
                $user->confirmed = 0;
                $user->confirm_str = $data_info['confirmation'];
                $user->following_count = 0;
                $user->postDatacount = 0;
                $user->followers_count = 0;
                $user->save();
                $fullname = $first_name." ".$last_name;
                $m = new SimpleEmailServiceMessage();
                $m->addTo($data_info['email']);
                $m->setFrom("Necked <basvlugt1990@outlook.com>");
                $m->setSubject('Welcome to Necked!');
                $messagestr = "You are ready to go!

                Hey, ".$fullname."

                We've finished setting up your Necked account. Just confirm your email to get started!

                This is confirmation code.

                ".$data_info['confirmation'];
            
            $m->setMessageFromString($messagestr);
            $sms_setting1 = env('sms_setting1');
            $sms_setting2 = env('sms_setting2');
            $ses = new SimpleEmailService($sms_setting1, $sms_setting2);
            $ses->sendEmail($m);

            $data = array(
                'action' =>'true',
                'result' => $user->toArray(),
                'confirmation' => $data_info['confirmation']
            );
            return $data;
            }
        }
        else if ($type == 'phone')
        {
            $check_username = User::where('username', '=', $username)->get()->toArray();
            $check_phonenumber = User::where('phone_number', '=', $phone_number)->get()->toArray();
            if(!empty($check_username) || !empty($check_phonenumber)){
                if(!empty($check_username)){
                    $data = array(
                        'action' =>'false',
                        'result' => 'username existed'
                    );
                    return $data;
                }
                if(!empty($check_phonenumber)){
                    $data = array(
                        'action' =>'false',
                        'result' => 'phonenumber existed'
                    );
                    return $data;
                }
            }
            else{

                $input = $request->all();
                $_token = str_random(25);
                $digits = 4;
                
                $data_info['confirmation'] = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
                $data_info['email'] = $email;
                $data_info['first_name'] = $first_name;
                $data_info['last_name'] = $last_name;
                $user = new User();
                $expire_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + 15 days'));
                $user->expire_date = $expire_date;
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->username = $username;
                $user->email = $email;       
                $user->password = $password;
                $user->profile_pic_url = 'https://www.sparklabs.com/forum/styles/comboot/theme/images/default_avatar.jpg';
                $user->profile_pic_thumbnail = 'https://www.sparklabs.com/forum/styles/comboot/theme/images/default_avatar.jpg';
                $user->_token = $_token;
                $user->phone_number = $phone_number;
                $user->confirmed = 0;
                $user->confirm_str = $data_info['confirmation'];
                $user->following_count = 0;
                $user->postDatacount = 0;
                $user->followers_count = 0;
                $user->save();
            $this->sendMessage($phone_number,$data_info['confirmation']);                    

            $data = array(
                'action' =>'true',
                'result' => $user->toArray(),
                'confirmation' => $data_info['confirmation']
            );
            return $data;
            }
        }
        else if($type == 'facebook')
        {
            $check_username = User::where('username', '=', $username)->get()->toArray();
            $check_email = User::where('email', '=', $email)->get()->toArray();
            if(!empty($check_username) || !empty($check_email)){
                if(!empty($check_username)){
                    $data = array(
                        'action' =>'false',
                        'result' => 'username existed'
                    );
                    return $data;
                }
                if(!empty($check_email)){
                    $data = array(
                        'action' =>'false',
                        'result' => 'email existed'
                    );
                    return $data;
                }
            }
            else{
                $input = $request->all();
                $_token = str_random(25);
                $digits = 4;
                
                $data_info['confirmation'] = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
                $data_info['email'] = $email;
                if($this->valid_email($email) != true)
                {
                    $data = array(
                        'action'=> 'false',
                        'result'=> 'invalid email'
                    );
                    return $data;
                }
                
                $data_info['first_name'] = $first_name;
                $data_info['last_name'] = $last_name;
                $user = new User();
                $expire_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + 3 minutes'));
                $user->expire_date = $expire_date;
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->username = $username;
                $user->email = $email;       
                $user->password = $password;
                $user->profile_pic_url = 'https://www.sparklabs.com/forum/styles/comboot/theme/images/default_avatar.jpg';
                $user->profile_pic_thumbnail = 'https://www.sparklabs.com/forum/styles/comboot/theme/images/default_avatar.jpg';
                $user->_token = $_token;
                $user->confirmed = 1;
                $user->confirm_str = $data_info['confirmation'];
                $user->following_count = 0;
                $user->postDatacount = 0;
                $user->followers_count = 0;
                $user->save();
                $data = array(
                    'action' =>'true',
                    'result' => $user->toArray(),
                    'confirmation' => $data_info['confirmation']
                );
            return response()->json($data);
            }
        }
        
    }
    public function sendMessage($phone_number,$body)
    {
    $sid    = "";
    $token  = "";
    $twilio = new Client($sid, $token);
    $receiver = "+".$phone_number;
    $message = $twilio->messages
                    ->create($receiver, // to
                            array("from" => "", "body" => $body)
                    );
    return $message;

  }
    public function get_userinfo(Request $request)
    {
        $uid =$request->get('user_id');
        $user = User::where('_id',$uid)->get()->toArray();
        if(!empty($user)){
            $data = array(
                'action' => 'true',
                'result'=>$user[0]
            );
        }else{
            $data = array(
                'action' => 'false',
                'result'=> 'No info'
    
            );
        }
        return $data;
    }
    public function get_userinfo_postdata(Request $request)
    {
        $uid = $request->get('user_id');
        $object_id = new objectID($uid);
        $user_info = User::raw(function($collection) use($object_id){
            return $collection->aggregate([
                [
                    '$match'=>[
                        '_id'=>$object_id
                    ],
                    '$lookup'=> [
                        'from'=> 'Media',
                        'localField'=> '_id',
                        'foreignField'=> 'object_postUserId',
                        'as' => 'postDatainfo'
                    ]
                ]
            ]);
        })->toArray();
        $data = array(
            'action' => 'success',
            'result' => $user_info
        );
        return $data;
    }
    public function get_userinfo_and_top_postdata(Request $request)
    {
        $selected_uid = $request->get('selected_user_id');
        $uid = $request->get('user_id');
        $init = $request->get('init');
        $limit_date = date('Y-m-d h:i:m',strtotime('-7 days'));
        $iso_limit_date = new UTCDateTime(new DateTime($limit_date));

        $following_info = Following::raw(function($collection) use($uid, $selected_uid){
            return $collection->aggregate([
                [
                    '$match' => [
                        'uid' =>$uid
                    ]
                ],
                [
                    '$unwind' => '$following_users'
                ],
                [
                    '$match' => [
                        'following_users.string_id' =>$selected_uid
                    ]
                ],
                [
                    '$group'  => ['_id' =>'$following_users.string_id', 'count' =>['$sum' =>1]]
                ]
            ]);
        })->toArray();
        if(!empty($following_info))
        {
            $following_state = 'true';
        }
        else{
            $following_state = 'false';
        }

        $data = User::where('_id',$selected_uid)->get()->toArray();

        if(!empty($init))
        {
            $postdata = Media::raw(function($collection) use($selected_uid){
                return $collection->aggregate([
                    [
                        '$match' => [
                            'postUserId' => $selected_uid,
                            'status'=> 2,
                        ]
                    ],
                    [
                        '$project'=>[
                                'postDataId'=>'$_id',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category',
                                'postDataLikeCount' => 1,
                                'postDataContent' => 1,
                                'created_at' => 1
                        ]
                    ],
                    [
                        '$sort' => [
                            'postDataLikeCount' => -1,
                            'created_at' => -1
                        ]
                    ],
                    [
                        '$limit'=>9
                    ]
                ]);
            })->toArray();
        }
        else
        {
            $after = $request->get('after');
            $after_info = Media::where('_id', $after)->get()->toArray();
            $postDataLikeCount =$after_info[0]['postDataLikeCount'];
            $created_at =$after_info[0]['created_at'];
            $date = new UTCDateTime(new DateTime($created_at));
            $postdata = Media::raw(function($collection) use($selected_uid, $postDataLikeCount, $date){
                return $collection->aggregate([
                    [
                        '$match' => [
                            'postUserId' => $selected_uid,
                            'postDataLikeCount'=>[
                                '$lte'=> $postDataLikeCount
                            ],
                            'created_at' => [
                                '$lt' => $date
                                ],
                            'status'=> 2,
                        ]
                    ],
                    [
                        '$project'=>[
                                'postDataId'=>'$_id',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'postDataLikeCount' => 1,
                                'created_at' => 1,
                                'postDataContent' => 1,
                        ]
                    ],
                    [
                        '$sort' => [
                            'postDataLikeCount' => -1,
                            'created_at' => -1
                        ]
                    ],
                    [
                        '$limit'=>9
                    ]
                ]);
            })->toArray();
        }
        $followers = Following::raw(function($collection) use($selected_uid){
            return $collection->aggregate([
                [
                    '$unwind' => '$following_users'
                ],
                [
                    '$match' => [
                        'following_users.string_id' => $selected_uid
                    ]
                ],
                [
                    '$group' => ['_id'=>'$following_users.string_id', 'count'=>['$sum'=>1]]
                ]
            ]);
        })->toArray();
        if(!empty($followers))
        {
            $followers_count = $followers[0]['count'];
        }
        else{
            $followers_count = 0;
        }
        if(!empty($data))
        {
            if(count($postdata) == 9)
            {
                $data['0']['after'] = $postdata[8]['_id'];
            }
            else{
                $data['0']['after'] = 'end';
            }
            $data[0]['postDataUserId'] = $data[0]['_id'];
            $data[0]['postdata'] = $postdata;
            $data[0]['followers_count'] = $followers_count;
            $data[0]['following_state'] = $following_state;
            $json_data = $data[0];
            $data = array(
                'action'=>'true',
                'result' => $json_data
            );
        }
        else{
            $data = array(
                'action' =>'false',
                'result'=> 'No post'
            );
        }
        return response()->json($data);
    }
    public function get_userinfo_and_recent_postdata(Request $request)
    {
        $selected_uid = $request->get('selected_user_id');
        $uid = $request->get('user_id');
        $init = $request->get('init');
        $following_info = Following::raw(function($collection) use($uid, $selected_uid){
            return $collection->aggregate([
                [
                    '$match' => [
                        'uid' =>$uid
                    ]
                ],
                [
                    '$unwind' => '$following_users'
                ],
                [
                    '$match' => [
                        'following_users.string_id' =>$selected_uid
                    ]
                ],
                [
                    '$group'  => ['_id' =>'$following_users.string_id', 'count' =>['$sum' =>1]]
                ]
            ]);
        })->toArray();
        if(!empty($following_info))
        {
            $following_state = 'true';
        }
        else{
            $following_state = 'false';
        }

        $data = User::where('_id',$selected_uid)->get()->toArray();

        if(!empty($init))
        {
            $postdata = Media::raw(function($collection) use($selected_uid){
                return $collection->aggregate([
                     [
                         '$match' => [
                             'postUserId' => $selected_uid,
                             'status'=> 2,
                         ]
                     ],
                     [
                        '$project'=>[
                                'postDataId'=>'$_id',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'created_at' => 1
                        ]
                    ],
                     [
                         '$sort' =>[
                             'created_at' => -1
                         ]
                     ],
                     [
                         '$limit'=> 9
                     ]
                 ]);
             })->toArray();
        }
        else
        {
            $after = $request->get('after');
            $after_info = Media::where('_id', $after)->get()->toArray();
            $created_at =$after_info[0]['created_at'];
            $iso_limit_date = new UTCDateTime(new DateTime($created_at));
            $postdata = Media::raw(function($collection) use($iso_limit_date, $selected_uid){
                return $collection->aggregate([
                     [
                         '$match' => [
                             'postUserId' => $selected_uid,
                             'created_at' => [
                                 '$lt' => $iso_limit_date
                             ],
                             'status'=> 2,
                         ]
                     ],
                     [
                        '$project'=>[
                                'postDataId'=>'$_id',
                                'postDataType'=>'$type',
                                'postDataiphonethumbnail'=>'$iphone_thmubnail',
                                'postDataipadthumbnail'=>'$ipad_thmubnail',
                                'postDataandroidthumbnail'=>'$android_thmubnail',
                                'postDataCategory'=>'$category', 
                                'created_at' => 1
                        ]
                    ],
                     [
                         '$sort' =>[
                             'created_at' => -1
                         ]
                     ],
                     [
                         '$limit'=> 9
                     ]
                 ]);
             })->toArray();
        }
        
        $followers = Following::raw(function($collection) use($selected_uid){
            return $collection->aggregate([
                [
                    '$unwind' => '$following_users'
                ],
                [
                    '$match' => [
                        'following_users.string_id' => $selected_uid
                    ]
                ],
                [
                    '$group' => ['_id'=>'$following_users.string_id', 'count'=>['$sum'=>1]]
                ]
            ]);
        })->toArray();
        if(!empty($followers))
        {
            $followers_count = $followers[0]['count'];
        }
        else{
            $followers_count = 0;
        }
        if(!empty($data))
        {
            if(count($postdata) == 9)
            {
                $data['0']['after'] = $postdata[8]['_id'];
            }
            else{
                $data['0']['after'] = 'end';
            }
            $data[0]['postDataUserId'] = $data[0]['_id'];
            $data[0]['postdata'] = $postdata;
            $data[0]['followers_count'] = $followers_count;
            $data[0]['following_state'] = $following_state;
            $json_data = $data[0];
            $data = array(
                'action'=>'true',
                'result' => $json_data
            );
        }
        else{
            $data = array(
                'action' =>'false',
                'result'=> 'No post'
            );
        }
        return response()->json($data);
    }
    function valid_email($email) {
        return !!filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public function signin(Request $request)
    { 
        $identify = $request->get('username');       
        $password = $request->get('password');
        // $hashed = Hash::make($password);
        // return $hashed;
        $check_email_or_username = $this->valid_email($identify);
        // $encrypt_pw = Crypt::encrypt($password);
        if($check_email_or_username == true){
            User::where('email', $identify)->get()->toArray();
            // $login = User::where('email', '=', $identify)->where('password', '=', $encrypt_pw)->get()->toArray();
            $login = User::where('email', '=', $identify)->where('password', '=', $password)->get()->toArray();
        }
        else{
            // $login = User::where('username', '=', $identify)->where('password', '=', $encrypt_pw)->get()->toArray();
            $login = User::where('username', '=', $identify)->where('password', '=', $password)->get()->toArray();
        }
        if(!empty($login)){
           	
            if($login[0]['confirmed'] == 0){
                $data = array(
                    'action' =>'false',
                    'result' => 'email was not verified'
                );
            }
            else{
            	$update_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + 3 minutes'));
            	$update_data = array(
                    'expire_date' => $update_date
                );
                DB::collection('User')->where('_id', $login[0]['_id'])->update($update_data);

                $data = array(
                    'action' =>'true',
                    'result' => $login[0]
                );
            }
            
        }else{
            $data = array(
                'action' =>'false',
                'result' => "you didn't register"
            );
        }
        
        return $data;
    }
    public function reset_password(Request $request)
    {
        $user_id = $request->get('user_id');
        $current_password = $request->get('current_password');
        $new_password = $request->get('new_password');
        $encrypt_pw = $current_password;
        $user= User::find($user_id);
        if($user->password == $encrypt_pw){
            $user->password = $new_password;
            $user->save();
            $data=array(
                'action'=>'true',
                'result'=>'set new password'
            );
        }
        else{
            $data=array(
                'action'=>'false',
                'result'=>'Current password is not matched'
            );
        }
        return $data;
    }
    
    public function forgot_password(Request $request)
    {
        $user_id = $request->get('user_id');
        $email = $request->get('email');
        $email = strtolower($email);
        $check = $this->valid_email($email);
        if($check == true)
        {
            // $user = User::where('email', $email)->first();
            $user = User::raw(function($collection) use($email)
                {
                    return $collection->aggregate([
                        [
                            '$project'=>[
                                'lower_email'=> [ '$toLower' => '$email' ],
                                'username' => 1,
                                'email' => 1,
                            ]
                        ],
                        [
                            '$match' => [
                                'lower_email'=> $email
                            ]
                        ]
                    ]);
                })->toArray();
            if(empty($user)){
                $data = array(
                    'action' =>'false',
                    'result' =>'your email is not existed'
                );
                return $data;
            }
            $digits = 6;
            $new_password = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
            $data = array(
                'password' =>$new_password
            );
            $email = $user[0]['email'];
            DB::collection('User')->where('email', $email)->update($data);
            $data_info['username'] = $user[0]['username'];
            $data_info['email'] = $user[0]['email'];
            $data_info['new_password'] = $new_password;

            $m = new SimpleEmailServiceMessage();
            $m->addTo($data_info['email']);
            $m->setFrom("Necked <basvlugt1990@outlook.com>");
            $m->setSubject('Reset Password');
            $messagestr = "

            We received a request to reset your password.

            So your password has been reset.

            To sign in with your new password. 

            ".$new_password;
            
            $m->setMessageFromString($messagestr);
            $sms_setting1 = env('sms_setting1');
            $sms_setting2 = env('sms_setting2');
            $ses = new SimpleEmailService($sms_setting1, $sms_setting2);
            $ses->sendEmail($m);
            $data=array(
                'action' => 'true',
                'result'=> 'send new password to your mail. please check.'
            );
        }else{
            $user = User::where('phone_number', $email)->first();
            if(empty($user)){
                $data = array(
                    'action' =>'false',
                    'result' =>'your email is not existed'
                );
                return $data;
            }
            $digits = 6;
            $new_password = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
            $data = array(
                'password' =>$new_password
            );
            DB::collection('User')->where('phone_number', $email)->update($data);
            $data_info['username'] = $user->username;
            $data_info['phone_number'] = $user->phone_number; 
            $data_info['new_password'] = $new_password;

            $this->sendMessage($email,$data_info['new_password']);
            $data=array(
                'action' => 'true',
                'result'=> 'send new password to your phpne. please check.'
            );
        }
        return $data;
    }
    // api/profile_edit?user_id=&first_name=&last_name=&email=
    public function profile_edit(Request $request)
    {
        $user_id = $request->get('user_id');
        $username = $request->get('username');
        $bio = $request->get('bio');
        $email = $request->get('email');
        $check_username = User::where('username', '=', $username)->where('_id','<>',$user_id)->get()->toArray();
        $check_email = User::where('email', '=', $email)->where('_id','<>',$user_id)->get()->toArray();
        if(!empty($check_username) || !empty($check_email)){
            if(!empty($check_username)){
                $data = array(
                    'action' =>'false',
                    'result' => 'username existed'
                );
                return $data;
            }
            if(!empty($check_email)){
                $data = array(
                    'action' =>'false',
                    'result' => 'email existed'
                );
                return $data;
            }
        }
        $update_data = array(
            'username' =>$username,
            'bio' =>$bio,
            'email'=>$email
        );
        User::where('_id', $user_id)->update($update_data);
        $user = User::where('_id',$user_id)->get()->toArray();
        $data=array(
            'action' => 'true',
            'result'=> $user[0]
        );
        return response()->josn($data);

    }
    // api/all_users?user_id
    public function all_users(Request $request)
    {
        $user_id = $request->get('user_id');
        $users = User::where('_id', '!=', $user_id)->where('confirmed',1)->get()->toArray();
        if(!empty($users))
        {
            $data = array(
            'action' =>true,
            'users' => $users
            );
        }
        else{
            $data = array(
            'action' =>false,
            'users' => 'No user'
            );
        }
        
        return response()->json($data);
    }
    // api/user_posts?user_id=
    public function get_posts(Request $request)
    {
        $uid = $request->get('user_id');
        $posts = Media::where('uid','=',$uid)->get()->toArray();
        if(!empty($posts))
        {
            $data = array(
                'action'=>'true',
                'result'=>$posts
            );
        }
        else{
            $data = array(
                'action'=>'false',
                'result'=>'noresult',
            );
        }
        return response()->json($data);
    }
    // api/user_like?user_id=&media_id=&like_state=
    public function user_like(Request $request)
    {
        $uid = $request->get('user_id');
        $media_id = $request->get('media_id');
        $like_state = $request->get('like_state');
        $Media = new Media();
        if($like_state == 1)
        {
            $Media->update_post($media_id, 'like');
            Media::where('_id', $media_id)->push('like_users', array(
                'uid' => $uid
                ));
            Media::where('_id', $media_id)->push('postdatalikecomment', array(
                    'uid' => $uid,
                    'object_uid' => new ObjectID($uid),
                    'type' =>'like'
                    ));
        }
        else{
            $object_id = new ObjectID($media_id);
            Media::raw()->findOneAndUpdate(['_id'=> $object_id], ['$pull'=> ['like_users'=> ['uid'=> $uid]]]);
            Media::raw()->findOneAndUpdate(['_id'=> $object_id], ['$pull'=> ['postdatalikecomment'=> ['uid'=> $uid, 'type'=>'like','object_uid' => new ObjectID($uid)]]]);
            $Media->update_post($media_id, 'unlike');
        }
        $data = array(
            'action' => 'true',
            'result' => 'success'
        );
        return response()->json($data);
    }
    public function user_dislike(Request $request)
    {
        $uid = $request->get('user_id');
        $media_id = $request->get('media_id');
        $dislike_state = $request->get('dislike_state');
        // $Media = new Media();
        if($dislike_state == 1)
        {
            Media::where('_id', $media_id)->increment('postDataDisLikeCount', 1);
            Media::where('_id', $media_id)->push('dislike_users', array(
                'uid' => $uid
                ));
        }
        else{
            $object_id = new ObjectID($media_id);
            Media::raw()->findOneAndUpdate(['_id'=> $object_id], ['$pull'=> ['dislike_users'=> ['uid'=> $uid]]]);
            Media::where('_id', $media_id)->increment('postDataDisLikeCount', -1);
        }
        $data = array(
            'action' => 'true',
            'result' => 'success'
        );
        return response()->json($data);
    }
   
    // api/facebook_login?user_id=&password=&email=
   
    
    public function register_confirm(Request $request)
    {
        $confirm_str = $request->get('confirm_str');
        $user = User::where('confirm_str',$confirm_str)->first();
        
        if(!empty($user)){
            $query = array(
                'confirmed' =>1
            );
            $story = User::where('confirm_str', $confirm_str)->update(array('confirmed' => 1));
            $data = array(
                'action' => 'true',
                'result' => $user
            );
        }
        else{
            $data = array(
                'action' => 'false',
                'result' => 'confirm is not correct'
            );
        }

        return response()->json($data);

    }
    public function get_like_posts(Request $request)
    {
        $uid = $request->get('user_id');
        $init = $request->get('init');
        if(!empty($init))
        {
            $data = Media::raw(function($collection) use($uid){
                return $collection->aggregate([
                    ['$sort' => [
                        'created_at'=>-1
                    ]],
                    [
                        '$match' => [
                            'status' => 1
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
                                ],
                                'result'=>[
                                    '$filter' => ['input'=>'$like_users',
                                    'as' => 'user_item',
                                    'cond' => [ '$eq'=> [ '$$user_item.uid', $uid ] ]
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
                    [
                        '$unwind' => '$result'
                    ],
                    [ '$limit' => 9 ]
                ]);
            })->toArray();
        }
        else{
            $after = $request->get('after');
            $after_info = Media::where('_id', $after)->get()->toArray();
            $created_at =$after_info[0]['created_at'];
            $date = new UTCDateTime(new DateTime($created_at));
            $data = Media::raw(function($collection) use($date, $uid){
                return $collection->aggregate([
                    ['$sort' => [
                        'created_at'=>-1
                    ]],
                    [
                        '$match' => [
                            'created_at' => [
                                '$lt' => $date
                                ],
                            'status' => 1
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
                                ],
                                'result'=>[
                                    '$filter' => [
                                        'input'=>'$like_users',
                                    'as' => 'user_item',
                                    'cond' => [ '$eq'=> [ '$$usre_item.uid', $uid ] ]
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
            }
            else{
                $send_info['data']['after'] = 'end';
            }
            $send_info['action'] = 'true';
            
        }
        else{
            $send_info['action'] = 'false';
        }
        
        return response()->json($send_info);
    }
    public function get_feed_posts(Request $request)
    {
        $uid = $request->get('user_id');
        $data = Media::raw(function($collection) use($uid)
        {
            return $collection->aggregate([
                [
                    '$match' => [
                        'postUserId'=> $uid,
                    ]
                ],
                [
                    '$unwind'=> '$postdatalikecomment'
                ],
                [
                    '$group' => [
                        '_id' =>'$postdatalikecomment.uid',
                        'object_uid' =>[
                                        '$first' => '$postdatalikecomment.object_uid'
                                    ],
                        'count' => [
                            '$sum' => 1
                            
                        ]
                        
                    ]
                
                ],
                [
                    '$lookup' =>[
                        'from' => 'User',
                        'localField' => 'object_uid',
                        'foreignField' => '_id',
                        'as' => 'user_info'
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
                        'profile_pic_thumbnail'=>'$user_info.profile_pic_thumbnail',
                        'postDatacount' => '$user_info.postDatacount',
                
                    ]
                ]
                
                
            ]);
        })->toArray();
        if(!empty($data))
        {
            $send_data = array(
                'action' => 'true',
                'result' => $data
            );
        }
        else{
            $send_data = array(
                'action' => 'false',
                'result' => 'no result'
            );
        }
        return response()->json($send_data);
    }
    public function token_auth(Request $request)
    {
        $token = $request->get('token');
        // 1537801500
        $token_result = User::where('_token', $token)->get()->toArray();
        if(!empty($token_result) && $token_result[0]['confirmed'] == 1)
        {
            $expire_date = strtotime($token_result[0]['expire_date']);
            $today = strtotime(date('Y-m-d H:i:s'));
            if($expire_date >= $today)
            {
                $data = array(
                    'action' => 'true',
                    'result' => $token_result
                );
                $expire_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + 3 minutes'));
                $update_data = array(
                    'expire_date' => $expire_date
                );
                DB::collection('User')->where('_token', $token)->update($update_data);
            }
            else{
                $data = array(
                    'action' => 'false',
                    'result' => 'expire was ended'
                );
            }
        }
        else{
            $data = array(
                'action' => 'false',
                'result' => 'No register or confirm was not'
            );
        }
        return $data;
    }
    public function facebook_login(Request $request)
    {
        $uid = $request->get('user_id');
        $facebook_password = $request->get('password');
        $facebook_email = $request->get('email');
        $user = User::find($uid);
        $user->facebook_password = $facebook_password;
        $user->facebook_email =$facebook_email;
        $user->save();
        $data = array(
            'action' =>'true',
            'result'=>$user->toArray()
        );
        return $data;

    }
    public function facebook_register(Request $request)
    {
        $uid = $request->get('user_id');
    }
}
