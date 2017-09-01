<?php namespace Joanvt\ImagePicker\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Joanvt\ImagePicker\Requests\UploadRequest;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Validator;


trait ImagePicker {


    public $options = [];

    public function __construct ($options = [])
    {
        $this->options = [
            'upload_dir' => 'public', // It means Storage::disk('public')
            'upload_folder' => 'images/' . date('Y') . '/' . date('m') . '/' . date('d'),
            'file_name' => 'hash', // Availables: hash, original, numbered
            'auto_orient' => true,
            'min_width' => 200, // Could be null
            'min_height' => 200, // Could be null
            'crop' => true, // It returns a crop version to client so you can access like this: image.versions.crop.url
            'versions' => [
                'avatar' => // At least width or height must be defined
                    [
                        'upload_dir' => 'public',
                        'keep_ratio' => true,
                        'upsize' => true,
                        'width' => 200,
                        'height' => 200
                    ]

            ]
        ];
    }

    public function load (Request $request){

        $file = $request->get('file');
        $path = $request->get('path');
        //$width = $request->get('width');
        $rotate = $request->get('rotate');

        $image = Storage::disk($this->options['upload_dir'])->get($path.'/'.$file);

        if($rotate){
            $angle = ($rotate < 0) ? abs($rotate) : 360 - $rotate;
            $image = Image::make($image);
            $image = $image->rotate($angle)->stream();
        }


        return response($image)->header('Content-Type', 'image/jpeg')->header('Content-Length',Storage::disk($this->options['upload_dir'])->size($path.'/'.$file));


    }

    public function upload (UploadRequest $request){

        $response = new \StdClass();


        if ($request->action === 'upload') {
            $file = $request->file('file');
            $response = $this->uploadHandler($file, $request);
        } elseif ($request->action === 'crop'){
            $file = $request->get('image');
            $path = $request->get('path');


            if($request->input('coords')){
                $w = $request->input('coords.w');
                $h = $request->input('coords.h');

                $x = $request->input('coords.x');
                $y = $request->input('coords.y');
                // $x2 = $request->input('coords.x2');
                // $y2 = $request->input('coords.y2');
                $response = $this->cropAction($file,$path,$x,$y,$w,$h);
            }else{
                $response = $this->cropAction($file,$path,null,null,null,null);
            }
            $this->cropped($response,$request);
        }

        return response()->json($response);
    }

    public function uploadHandler ($file, Request $request){

        $this->beforeUpload($request);

        $response = new \StdClass();

        if ($this->options['file_name'] == 'original') {
            $stored = $file->storeAs($this->options['upload_folder'], $file->getClientOriginalName(), $this->options['upload_dir']);
        } elseif ($this->options['file_name'] == 'hash') {
            $stored = $file->store($this->options['upload_folder'], $this->options['upload_dir']);
        }

        $fileUrl = Storage::disk($this->options['upload_dir'])->url($stored);
        $img = Storage::disk($this->options['upload_dir'])->get($stored);

        $response->type = pathinfo($stored, PATHINFO_EXTENSION);
        $response->name = pathinfo($stored, PATHINFO_FILENAME) . '.' . $response->type;
        $response->size = Storage::disk($this->options['upload_dir'])->size($stored);
        $response->path = $this->options['upload_folder'];
        $response->url = $fileUrl;

        list($response->width, $response->height) = @getimagesizefromstring($img);

        $this->uploaded($response, $request);

        return $response;
    }

    public function cropAction ($image, $path, $src_x, $src_y, $dst_w, $dst_h, $rotate = null){

        $tmpUrl = $path.'/crop/'.$image;
        $src_x = ceil($src_x);
        $src_y = ceil($src_y);
        $dst_w = ceil($dst_w);
        $dst_h = ceil($dst_h);

        $response = new \StdClass();

        $imageFontSrc = Storage::disk($this->options['upload_dir'])->get($path.'/'.$image);
        if (!$imageFontSrc){
            return false;
        }

        $original = Image::make($imageFontSrc);

        if(!$dst_w) {
            $min_width = $this->options['min_width'] ? $this->options['min_width'] : null ;
            $min_height = $this->options['min_height'] ? $this->options['min_height'] : null ;
            $original->resize($min_width,$min_height);
        }else{
            $original->crop($dst_w, $dst_h, $src_x, $src_y);
        }

        Storage::disk('public')->put($tmpUrl, $original->stream());

        //$getTmpImage = Storage::disk($this->options['upload_dir'])->get($tmpUrl);
        //$response = Image::make($getTmpImage);

        $fileUrl = Storage::disk($this->options['upload_dir'])->url($path.'/'.$image);

        $response->type = pathinfo($image, PATHINFO_EXTENSION);
        $response->name = pathinfo($image, PATHINFO_FILENAME) . '.' . $response->type;
        $response->size = Storage::disk($this->options['upload_dir'])->size($tmpUrl);
        $response->path = $path;
        $response->url = $fileUrl;

        list($response->width, $response->height) = @getimagesizefromstring($imageFontSrc);

        $response->versions = [];



        $response->versions = $this->createVersions($tmpUrl,$path);
        if(isset($this->options['crop'])){
            $urlCrop = Storage::disk($this->options['upload_dir'])->url($path.'/crop/'.$image);
            $img = Storage::disk($this->options['upload_dir'])->get($path.'/crop/'.$image);
            list($widthCrop, $heightCrop) = @getimagesizefromstring($img);
            $response->versions['crop'] = [
                'type'   => pathinfo($fileUrl, PATHINFO_EXTENSION),
                'name'   => pathinfo($fileUrl, PATHINFO_FILENAME) . '.' . $response->type,
                'size'   => Storage::disk($this->options['upload_dir'])->size($path.'/crop/'.$image),
                'path'   => $path.'/crop',
                'url'    => $urlCrop,
                'width'  => $widthCrop,
                'height' => $heightCrop
            ];
        }

        return $response;
    }

    /*
     * $filename must be a String including the path.
     *
     * @param  string $filename
     * @param  string $version
     * @return string
     */
    public function getUploadPath ($name,$path){

        $response = new \stdClass;



        $fileUrl = Storage::disk($this->options['upload_dir'])->url($path.'/'.$name);
        $img = Storage::disk($this->options['upload_dir'])->get($path.'/'.$name);

        $response->type = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $response->name = pathinfo($fileUrl, PATHINFO_FILENAME) . '.' . $response->type;
        $response->size = Storage::disk($this->options['upload_dir'])->size($path.'/crop/'.$name);
        $response->path = $path;
        $response->url = $fileUrl;
        list($response->width, $response->height) = @getimagesizefromstring($img);

        $response->versions = [];

        if(isset($this->options['crop'])){
            $urlCrop = Storage::disk($this->options['upload_dir'])->url($path.'/crop/'.$name);
            $img = Storage::disk($this->options['upload_dir'])->get($path.'/crop/'.$name);
            list($widthCrop, $heightCrop) = @getimagesizefromstring($img);
            $response->versions['crop'] = [
                'type'   => pathinfo($fileUrl, PATHINFO_EXTENSION),
                'name'   => pathinfo($fileUrl, PATHINFO_FILENAME) . '.' . $response->type,
                'size'   => Storage::disk($this->options['upload_dir'])->size($path.'/'.$name),
                'path'   => $path.'/crop',
                'url'    => $urlCrop,
                'width'  => $widthCrop,
                'height' => $heightCrop
            ];
        }

        foreach ($this->options['versions'] as $version => $options) {

            $widthSetted = null;
            $heightSetted = null;

            if(isset($options['width']) && $options['width']){
                $widthSetted = $options['width'];
            }

            if(isset($options['height']) && $options['height']){
                $heightSetted = $options['height'];
            }

            if(!$widthSetted && !$heightSetted){
                return false;
            }


            if($widthSetted && $heightSetted){
                $folder = $path.'/thumbs/'.$widthSetted.'x'.$heightSetted;
            }elseif($widthSetted && !$heightSetted){
                $folder = $path.'/thumbs/'.$widthSetted.'x'.$widthSetted;
            }elseif($heightSetted && !$widthSetted){
                $folder = $path.'/thumbs/'.$heightSetted.'x'.$heightSetted;
            }



            $savePath = $folder.'/'.$name;

            $imgUrl = Storage::disk($options['upload_dir'])->url($savePath);


            $response->versions[$version] = array(
                'url'    => $imgUrl,
                'width'  => $widthSetted,
                'height' => $heightSetted
            );

        }

        return $response;

    }

    public function createVersions ($imagePath, $path, $is_upload = false){

        $versions = [];
        $getTmpImage = Storage::disk($this->options['upload_dir'])->get($imagePath);
        //$getTmpImageUrl = Storage::disk($this->options['upload_dir'])->url($path);

        foreach ($this->options['versions'] as $version => $options) {

            $widthSetted = null;
            $heightSetted = null;

            if(isset($options['width']) && $options['width']){
                $widthSetted = $options['width'];
            }

            if(isset($options['height']) && $options['height']){
                $heightSetted = $options['height'];
            }

            if(!$widthSetted && !$heightSetted){
                return false;
            }
            $img = Image::make($getTmpImage);

            $img->resize($widthSetted, $heightSetted, function ($constraint) use($options) {

                if(isset($options['keep_ratio']) && $options['keep_ratio']){
                    $constraint->aspectRatio();
                }

                if(isset($options['upsize']) && $options['upsize']){
                    $constraint->upsize();
                }
            });

            if($widthSetted && $heightSetted){
                $folder = $path.'/thumbs/'.$widthSetted.'x'.$heightSetted;
            }elseif($widthSetted && !$heightSetted){
                $folder = $path.'/thumbs/'.$widthSetted.'x'.$widthSetted;
            }elseif($heightSetted && !$widthSetted){
                $folder = $path.'/thumbs/'.$heightSetted.'x'.$heightSetted;
            }

            $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
            $name = pathinfo($imagePath, PATHINFO_FILENAME) . '.' . $ext;

            $savePath = $folder.'/'.$name;
            //dd($folder);
            Storage::disk($options['upload_dir'])->put($savePath,$img->stream());

            $imgUrl = Storage::disk($options['upload_dir'])->url($savePath);


            $versions[$version] = array(
                'url'    => $imgUrl,
                'width'  => $widthSetted,
                'height' => $heightSetted
            );

        }


        return $versions;
    }

    public function delete(Request $request){


        $validator = Validator::make($request->all(), [
            'file' => 'required|string',
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['Error' => 'Something went wrong'])->setStatusCode(422);
        }


        $file = $request->get('file');
        $path = $request->get('path');

        if(!$this->beforeDelete($file,$path,$request)){
            abort(404);
        }

        $delete = Storage::disk($this->options['upload_dir'])->delete($path.'/'.$file);

        $response = new \StdClass();

        if($delete){
            $response->deleted = true;
            $response->path = $path;
            $response->name = $file;
        };

        $this->deleted($response,$request);
    }

    public function autoload(Request $request){
        /*$image = $this->getUploadPath($name,$path);
        return response()->json($image);*/
    }

    public function cropped (\StdClass $response,Request $request){
        //
    }

    public function beforeUpload (Request $request){
        //
    }

    public function uploaded (\StdClass $response,Request $request){
        //
    }

    public function deleted (\StdClass $response,Request $request){
        // Once deleted you can update your user attached image, for example.
    }

    public function beforeDelete($file,$path,Request $request){
        // Check if the user can delete this file!
        // Check Laravel Authorization FYI as extra! Is not needed at all.
        return true;
    }

}