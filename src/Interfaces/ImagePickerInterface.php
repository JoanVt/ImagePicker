<?php namespace Joanvt\ImagePicker\Interfaces;

use Illuminate\Http\Request;
use Joanvt\ImagePicker\Requests\UploadRequest;

interface ImagePickerInterface
{

    public function __construct ($options = []);

    public function load (Request $request);

    public function upload (UploadRequest $request);

    public function getUploadPath ($name, $path);

    public function uploadHandler ($file,Request $request);

    public function cropAction ($image, $path, $src_x, $src_y, $dst_w, $dst_h, $rotate = null);

    public function createVersions ($imagePath,$path, $is_upload = false);

    public function delete (Request $request);

    public function cropped (\StdClass $response, Request $request);

    public function uploaded (\StdClass $response, Request $request);

    public function deleted (\StdClass $response, Request $request);

    public function beforeDelete($file,$path,Request $request);

    public function beforeUpload(Request $request);

    public function autoload(Request $request);

    public function checkImageName($nameImage, $extensionImage, $directory, $folder, $originalName = null, $counter = null);

}