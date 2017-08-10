<?php

namespace Joanvt\ImagePicker\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Joanvt\ImagePicker\Interfaces\ImagePickerInterface;
use Joanvt\ImagePicker\Traits\ImagePicker;
use Illuminate\Support\Facades\Storage;

class ImagePickerController extends Controller implements ImagePickerInterface{

    use ImagePicker;

    public $options = [];

    public function __construct($options = [])
    {
        $this->options = [
            'upload_dir' => Storage::disk('public')
        ];
    }

    public function load(){
        echo "HOLA QUE TAL";
        die();
    }


}