<?php

$configGroup = [];

if(!is_array(Config::get('imagepicker'))){
    Throw new Exception('ImagePicker needs a config file (Use php artisan vendor:publish --tag=ipm_config) ');
}

if(Config::get('imagepicker.routeGroup')){
    $configGroup = Config::get('imagepicker.routeGroup');
}


Route::group($configGroup,function(){
    Route::get('upload', 'Joanvt\ImagePicker\Controllers\ImagePickerController@load')->name('IMP_upload');
    Route::get('upload/autoload', 'Joanvt\ImagePicker\Controllers\ImagePickerController@autoload');
    Route::post('upload', 'Joanvt\ImagePicker\Controllers\ImagePickerController@upload')->name('IMP_upload');
    Route::delete('upload', 'Joanvt\ImagePicker\Controllers\ImagePickerController@delete')->name('IMP_delete');
});