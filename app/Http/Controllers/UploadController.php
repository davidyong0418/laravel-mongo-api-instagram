<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Following;
use App\Model\Follower;
use Image;
use App\Model\User;
use App\Model\Text;
use App\Model\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use App\Model\EventVideos;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use FFMpeg\FFMpeg;
use App\Model\Post;
use MongoDB\BSON\ObjectID;
use DB;
class UploadController extends Controller
{
    public function save_media_s3($file, $type){
        $media_path = 'necked'. '/'.$type.'/'.$file;
        $file_content = file_get_contents($file);
        // $filepath = Storage::disk('s3')->put($media_path, file_get_contents($file),'public');
        $filepath = Storage::disk('s3')->put($media_path,$file_content,'public');
        $saved_media_path = Storage::disk('s3')->url($media_path);
        return $saved_media_path;
    }
    // api/upload_image?upload_file=&user_id=
    public function upload_media(Request $request)
    {
        
        ini_set('max_execution_time', 300);
        $uid = $request->get('user_id');
        $type = $request->get('type');
        $category_id = $request->get('category');
        $caption = $request->get('caption');
        $all_request = $request->all();
        $upload_file = $all_request['upload_file'];

        $path = $upload_file->getClientOriginalExtension();
        $uploaddir = 'upload/original.'.$path;
        move_uploaded_file($upload_file, $uploaddir);
        $image_size = getimagesize($uploaddir);
        $image_width = $image_size[0];
        $image_height = $image_size[1];

        if($type == 'image')
        {
            $thumbnail_small = 'output_small_'.uniqid().'.jpg';
            $thumbnail_large = 'output_large_'.uniqid().'.jpg';
            $thumbnail_origin = $thumbnail_small;
            $query_iphone = 'ffmpeg -i '.$uploaddir.' -vf scale="400:-1" upload/'.$thumbnail_small;
            $query_ipad = 'ffmpeg -i '.$uploaddir.' -vf scale="1024:-1" upload/'.$thumbnail_large;
            $query= $query_iphone." && ".$query_ipad;
        }
        else{
            $thumbnail_small = 'output_small_'.uniqid().'.jpg';
            $thumbnail_large = 'output_large_'.uniqid().'.jpg';
            $thumbnail_origin = 'output_origin_'.uniqid().'.mov';
            $convert_ext = 'ffmpeg -i '.$uploaddir.' -acodec copy -vcodec copy -f mov upload/'.$thumbnail_origin;
            $query_iphone = 'ffmpeg -i '.$uploaddir.' -vframes 1 -filter:v scale="400:-1" upload/'.$thumbnail_small;
            $query_ipad = 'ffmpeg -i '.$uploaddir.' -vframes 1 -filter:v scale="1024:-1" upload/'.$thumbnail_large;
            $query= $convert_ext." && ".$query_iphone." && ".$query_ipad;
           
        }
        shell_exec($query);
        // if($type == 'video')
        // {
        //     $media_original_path = $this->save_media_s3($convert_video_path,$uid,$type,'original',$path);
        // }
        // else
        // {
        //     $media_original_path = $this->save_media_s3($uploaddir,$uid,$type,'original',$path);
        // }
        // $media_iphone_path = $this->save_media_s3('upload/output_iphone.jpg',$uid,$type,'iphone');
        // $media_ipad_path = $this->save_media_s3('upload/output_ipad.jpg',$uid,$type,'ipad');
        // $media_android_path = $this->save_media_s3('upload/output_android.jpg',$uid,$type,'android');
        array_map('unlink', glob("upload/original*"));
        $save_result = $this->save_media_db($uid,$type,$thumbnail_origin, $thumbnail_small,$thumbnail_large,$category_id,$caption);

        $data = array(
            'action'=>'true',
            'result' =>$save_result,
        );
        return response()->json($data);
    }
    public function get_base_url()
    {
        $hostName = $_SERVER['HTTP_HOST']; 
        // output: http://
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
        // return: http://localhost/myproject/
        return $protocol.$hostName."/upload/";
    }
    public function save_media_db($uid, $type, $media_original_path, $thumbnail_small,$thumbnail_large, $category_id, $caption)
    {
        $base_url = $this->get_base_url();
        $follow_count = Following::where('uid', $uid)->where('denyorrejectoption', true)->count();
        $today = date('Y-m-d h:i:s');
        $new = array();
        $object_postUserId = new ObjectID($uid);
        $new[]= array( 
            'uid' => new ObjectID($uid),
            'comment' => $caption,
            'comment_date' => $today);
        $media = new Media();
        $media->postUserId = $uid;
        $media->object_postUserId = $object_postUserId;
        $media->follow_count = $follow_count;
        $media->type = $type;
        $media->path = $base_url.$media_original_path;
        $media->iphone_thmubnail = $base_url.$thumbnail_small;
        $media->ipad_thmubnail = $base_url.$thumbnail_large;
        // $media->android_thmubnail = $media_android_path;
        $media->category = $category_id;
        $media->postDataCaption = $caption;
        $media->postDataLastCommentUserID = $uid;
        $media->postDataLastCommentText='';
        $media->postDataShareCount = 0;
        $media->postDataCommentCount = 0;
        $media->postDataLikeCount = 0;
        $media->postDataDisLikeCount = 0;
        $media->comments = $new;
        $media->like_users = array();
        $media->dislike_users = array();
        $media->postdatalikecomment = array();
        $media->tags = array();
        $media->status = 1;
        $media->upload_complete = false;
        $media->save();
        $this->increase_user_post_count($uid);
        return $media->toArray();
    }
    // api/upload_text
    public function upload_text(Request $request)
    {
        
        $uid = $request->get('user_id');
        $category = $request->get('category');
        $caption = $request->get('caption');
        $content = $request->get('text');
        $object_postUserId = new ObjectID($uid);
        $media = new Media();
        $media->postUserId = $uid;
        $media->object_postUserId = $object_postUserId;
        $media->follow_count = '';
        $media->type = 'text';
        $media->path = '';
        $media->iphone_thmubnail = '';
        $media->ipad_thmubnail = '';
        $media->category = $category;
        $media->postDataCaption = $caption;
        $media->postDataContent = $content;
        $media->postDataLastCommentUserID = $uid;
        $media->postDataLastCommentText='';
        $media->postDataShareCount = 0;
        $media->postDataCommentCount = 1;
        $media->postDataLikeCount = 0;
        $media->postDataDisLikeCount = 0;
        $media->comments = array();
        $media->like_users = array();
        $media->postdatalikecomment = array();
        $media->dislike_users = array();
        $media->tags = array();
        $media->status = 1;
        $media->save();
        $this->increase_user_post_count($uid);
        $data = array(
            'action'=>'true',
            'result' =>$media->toArray(),
        );
        return response()->json($data);
    }
    public function change_profile_pic(Request $request)
    {
        ini_set('max_execution_time', 300);
        $uid = $request->get('user_id');
        $all_request = $request->all();
        $upload_file = $all_request['upload_file'];
        $extension = $upload_file->getClientOriginalExtension();
        $uploaddir = 'upload/profile.'.$extension;
        move_uploaded_file($upload_file, $uploaddir);
        $query = 'ffmpeg -i '.$uploaddir.' -vf scale="200:-1" upload/profile_thumbnail.jpg';
        shell_exec($query);
        $profile_pic_original_path = $this->save_media_s3($uploaddir,$uid,'profile','original',$extension);
        $profile_pic_phone_path = $this->save_media_s3('upload/profile_thumbnail.jpg',$uid,'profile','phone');
        $save_result = $this->save_profile_pic($uid, $profile_pic_original_path, $profile_pic_phone_path);
        array_map('unlink', glob("upload/*"));
        $data = array(
            'action' => 'true',
            'result' => $save_result,
        );
        return response()->json($data);
    }
    public function save_profile_pic($uid, $original_path, $thumbnail_path)
    {
        User::where('_id', $uid)->update(array('profile_pic_url' => $original_path, 'profile_pic_thumbnail'=>$thumbnail_path));
        $user_info = User::find($uid);
        return $user_info;
    }
    public function increase_user_post_count($uid)
    {
        $user_data = User::where('_id', $uid)->get();
        if(empty($user_data[0]['postDatacount']))
        {
            User::where('_id', $uid)->update(array('postDatacount' => 1));
        }
        else{
            User::where('_id', $uid)->increment('postDatacount', 1);
        }
    }
    // cron job
    public function upload_media_s3()
    {
        $files = Media::where('upload_complete', false)->get()->toArray();
        foreach ($files as $file)
        {
            $origin = $file['path'];
            $small = $file['iphone_thmubnail'];
            $large = $file['ipad_thmubnail'];
            $type = $file['type'];
            $origin_media_path = substr($origin, strpos($origin, "upload"));
            $small_media_path = substr($small, strpos($small, "upload"));    
            $large_media_path = substr($large, strpos($large, "upload"));

            $s3_origin_path = $this->save_media_s3($origin_media_path,$type);
            $s3_small_path = $this->save_media_s3($small_media_path,$type);
            $s3_large_path = $this->save_media_s3($large_media_path,$type);

            $update_data = array(
                'path' => $s3_origin_path,
                'iphone_thmubnail' => $s3_small_path,
                'ipad_thmubnail' => $s3_large_path,
                'upload_complete' => true
            );
            DB::collection('Media')->where('path', $origin)->update($update_data);
            array_map('unlink', glob($origin_media_path));
            array_map('unlink', glob($small_media_path));
            array_map('unlink', glob($large_media_path));
        }
        
    }
    public function set_cronjob()
    {
        $this->upload_media_s3();
    }
}
