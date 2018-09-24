<?php
namespace App\Models\Backend;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class EventImages extends Model {
    /**
     * @var string
     */
    protected $table = 'event_images';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function uploadImage(Request $request) {
        $file_data = [];

        // Upload image
        if ($request->file('event_image')) {
            $path = Storage::putFile('event_images', $request->file('event_image'));
            $url = Storage::url($path);

            // Save image info to database
            $imageClass = new EventImages();
            $imageClass->event_id = Input::get('event_id');
            $imageClass->name = $request->file('event_image')->getClientOriginalName();
            $imageClass->size = $request->file('event_image')->getClientSize();
            $imageClass->image = $path;
            $imageClass->image_url = $url . '?' . time();
            $imageClass->ordering = 99999999;
            $imageClass->save();

            $file_data = [
                'id' => $imageClass->id,
                'name' => $request->file('event_image')->getClientOriginalName(),
                'size' => $request->file('event_image')->getClientSize(),
                'title' => '',
                'artist' => '',
                'year' => '',
                'link' => '',
                'url' => $url,
                'path' => $path,
                'deleteType' => 'GET',
                'deleteUrl' => url('/event/delete_image?id='. $imageClass->id)
            ];
        }

        return $file_data;
    }
}
