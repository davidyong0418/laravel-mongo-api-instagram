<?php
namespace App;

use Illuminate\Http\Request;
// use Illuminate\Database\Eloquent\Model;
// use Jenssegers\Mongodb\Eloquent\Model;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Input;
// use FFMpeg\FFMpeg;
// use FFMpeg\Coordinate\TimeCode;

class EventVideos{
    /**
     * @var string
     */
    protected $connection = 'mongodb';
    // protected $table = 'event_videos';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function uploadVideo(Request $request) {
        $file_data = [];
        print_r('asdfasdsa');
        exit;
        // Upload image
        if ($request->file('event_video')) {
            /**
             * Generate video thumbnail
             */
            // $path = Storage::putFile('ffmpeg_thumbnail', $request->file('event_video'));
            // $video_path = storage_path() .'/'. $path;
            // $thumbnail_image = storage_path() . '/ffmpeg_thumbnail/thumbnail_'. time() .'.jpg';
            // $ffmpeg = FFMpeg::create();
            // $video = $ffmpeg->open($video_path);
            // $frame = $video->frame(TimeCode::fromSeconds(1));
            // $frame->save($thumbnail_image);
            // exit;
            ///////////////////////

            $path = Storage::putFile('videos', $request->file('upload_file'));
            $url = Storage::url($path);

            // Save image info to database
            $videoClass = new EventVideos();
            // $videoClass->event_id = random_str(6);
            // $videoClass->name = $request->file('upload_file')->getClientOriginalName();
            // $videoClass->size = $request->file('upload_file')->getClientSize();
            // $videoClass->thumbnail = $path;
            // $videoClass->thumbnail_url = $url . '?' .time();
            // $videoClass->ordering = 99999999;
            // $videoClass->save();

            $file_data = [
                'id' => 222222222,
                'name' => $request->file('upload_file')->getClientOriginalName(),
                'size' => $request->file('upload_file')->getClientSize(),
                'url' => $url,
                'path' => $path,
                'video_url' => '',
                'description' => '',
                'deleteType' => 'GET',
            ];
        }

        return $file_data;
    }
}
