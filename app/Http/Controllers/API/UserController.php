<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
Use App\Following;
Use App\Media;
Use App\Like;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Mail;
use DB;
use SimpleEmailServiceMessage;
use SimpleEmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController extends Controller
{


    public $successStatus = 200;


    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'unique:User,username',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['action'=>'false', 'result' => 'required field'], 401);     
        }
        else{
            $check_username = User::where('username', '=', $username)->get()->toArray();
            $check_email = User::where('email', '=', $email)->get()->toArray();
        }
        if(!empty($check_username) || !empty($check_email)){
            if(!empty($check_username)){
                $data = array(
                    'action' =>'false',
                    'result' => 'username existed'
                );
                return response()->json($data);
            }
            if(!empty($check_email)){
                $data = array(
                    'action' =>'false',
                    'result' => 'email existed'
                );
                return response()->json($data);
            }
        }
        else
        {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['_token'] = $user->createToken('MyApp')->accessToken;
            $input['confirmed'] = 0;
            $input['confirm_str'] = str_pad(rand(0, pow(10, $digits)-1), 4, '0', STR_PAD_LEFT);
            $user = User::create($input);
            return response()->json(['action'=>'true', 'result'=>$user]);
        }
    }
}
