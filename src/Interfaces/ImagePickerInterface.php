<?php namespace Joanvt\ImagePicker\Interfaces;

use Illuminate\Http\Request;
use Joanvt\ImagePicker\Requests\UploadRequest;

interface ImagePickerInterface
{

    public function __construct ($options = []);

    public function load (Request $request);

    public function upload (UploadRequest $request);

    public function getUploadPath ($image = '', $version = '');

    public function uploadHandler ($image);

    public function cropAction ($image, $path, $src_x, $src_y, $dst_w, $dst_h, $rotate = null);

    public function createVersions ($imagePath,$path, $is_upload = false);

    public function delete (Request $request);

    public function cropped (\StdClass $response);

    public function uploaded (\StdClass $response);

    public function deleted (\StdClass $response);

    public function beforeDelete();

    public function beforeUpload($file,$path);

    public function autoload();

}