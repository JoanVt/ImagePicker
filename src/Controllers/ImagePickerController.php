<?php

namespace Joanvt\ImagePicker\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Joanvt\ImagePicker\Interfaces\ImagePickerInterface;
use Joanvt\ImagePicker\Traits\ImagePicker;
use Illuminate\Support\Facades\Storage;
use Joanvt\ImagePicker\Requests\UploadRequest;
use Intervention\Image\ImageManagerStatic as Image;


class ImagePickerController extends Controller implements ImagePickerInterface{

    use ImagePicker;

    public $options = [];

    public function __construct($options = [])
    {
        $this->options = [
            'upload_dir' => 'public', // It means Storage::disk('public')
            'upload_folder' => 'images/'.date('Y').'/'.date('m').'/'.date('d'),
            'file_name' => 'original', // Availables: hash, original
            'auto_orient' => true,
            'versions' => [
                [
                 'avatar' =>
                     [
                        'upload_dir' => 'public',
                        'crop' => true,
                        'width' => 200,
                        'height' => 200
                     ]
                ]


            ]
        ];
    }

    public function load(Request $request){

        $file = $request->get('file');
        $path = $request->get('path');
        $width = $request->get('width');
        $rotate = $request->get('rotate');

        $image = Storage::disk($this->options['upload_dir'])->get($path.'/'.$file);

        if($rotate){
            $angle = ($rotate < 0) ? abs($rotate) : 360 - $rotate;
            $image = Image::make($image);
            $image = $image->rotate($angle)->stream();
        }

        return response($image)->header('Content-Type', 'image/jpeg');


    }

    public function upload(UploadRequest $request){

        $response = new \StdClass();


        if($request->action === 'upload') {
            $file = $request->file('file');
            $response = $this->uploadHandler($file);
        }elseif($request->action === 'crop'){
            $file = $request->get('image');
            $path = $request->get('path');
            $width = $request->get('width');
            $height = $request->get('height');
            $x = $request->input('coords.x');
            $y = $request->input('coords.y');

            $x2 = $request->input('coords.x2');
            $y2 = $request->input('coords.y2');

            $response = $this->cropAction($file,$path,$width,$height,$x,$y,$x2,$y2);
        }


        return response()->json($response);
    }

    public function uploadHandler($file){

        $response = new \StdClass();

        if ($this->options['file_name'] == 'original') {
            $stored = $file->storeAs($this->options['upload_folder'], $file->getClientOriginalName(), $this->options['upload_dir']);
        } elseif ($this->options['file_name'] == 'hash') {
            $stored = $file->store($this->options['upload_folder'], $this->options['upload_dir']);
        }

        $fileUrl = Storage::disk($this->options['upload_dir'])->url($stored);

        $response->type = pathinfo($stored, PATHINFO_EXTENSION);
        $response->name = pathinfo($stored, PATHINFO_FILENAME) . '.' . $response->type;
        $response->size = $file->getClientSize();
        $response->path = $this->options['upload_folder'];
        $response->url = $fileUrl;

        list($response->width, $response->height) = @getimagesize($file);

        return $response;
    }

    public function cropAction($image,$path,$width, $height, $x, $y, $x2,$y2, $rotate = null){

        $response = new \StdClass();
        $image_src = Storage::disk($this->options['upload_dir'])->get($path.'/'.$image);

        $img = Image::make($image_src);
        $img->crop($width,$height,ceil($x),ceil($y));

        $tmpUrl = $path.'/crop/'.$image;
        Storage::disk($this->options['upload_dir'])->put($tmpUrl,$img->stream());

        if($rotate){

            $angle = ($rotate < 0) ? abs($rotate) : 360 - $rotate;
            $image = Image::make($image);
            $image = $image->rotate($angle)->stream();
        }

        $fileUrl = Storage::disk($this->options['upload_dir'])->url($path.'/'.$image);

        $response->type = pathinfo($image, PATHINFO_EXTENSION);
        $response->name = pathinfo($image, PATHINFO_FILENAME) . '.' . $response->type;
        $response->size = $img->filesize();
        $response->path = $path;
        $response->url = $fileUrl;

        return $response;
    }
    /*
     * $filename must be a String including the path.
     *
     * @param  string $filename
     * @param  string $version
     * @return string
     */
    public function getUploadPath($filename = '', $version = ''){

        $upload_dir = $this->options['upload_folder'];

        if ($version != '') {
            $dir = @$this->options['versions'][$version]['upload_dir'];

            if (!empty($dir)) {
                $upload_dir = $dir;
            }
        }

        return $upload_dir .'/'. $filename;

    }

}