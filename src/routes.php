<?php

$configGroup = [];

if(!Config::get('imagepicker')){
    Throw new Exception('ImagePicker needs a config file (Use php artisan vendor:publish --tag=ipm_config) ');
}

if(Config::get('imagepicker.routeGroup')){
    $configGroup = Config::get('imagepicker.routeGroup');
}


Route::group($configGroup,function(){
    Route::get('load', 'Joanvt\ImagePicker\Controllers\ImagePickerController@load')->name('IMP_load');
    Route::get('upload', 'Joanvt\ImagePicker\Controllers\ImagePickerController@upload')->name('IMP_upload');

});