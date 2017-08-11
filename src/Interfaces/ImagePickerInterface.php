<?php namespace Joanvt\ImagePicker\Interfaces;

use Illuminate\Http\Request;
use Joanvt\ImagePicker\Requests\UploadRequest;

interface ImagePickerInterface
{

    public function __construct($options = []);

    public function load(Request $request);

    public function upload(UploadRequest $request);

    public function getUploadPath($image = '', $version = '');

    public function uploadHandler($image);

    public function cropAction($image, $path, $width, $height, $x, $y, $x2, $y2, $rotate = null);

}