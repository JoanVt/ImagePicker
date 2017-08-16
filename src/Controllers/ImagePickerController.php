<?php

namespace Joanvt\ImagePicker\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Joanvt\ImagePicker\Interfaces\ImagePickerInterface;
use Joanvt\ImagePicker\Traits\ImagePicker;


class ImagePickerController extends Controller implements ImagePickerInterface {

    use ImagePicker;


}