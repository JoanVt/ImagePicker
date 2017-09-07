# ImagePicker by JoanVt
Image cropper for Laravel 5.4 
You must get ImagePicker from Envato - [ImagePicker](https://codecanyon.net/item/imagepicker-uploader-webcam-cropper/6722532?s_rank=1)

Just **14$** and you will get a nice jQuery plugin for uploading and cropping images.

## Installation

```
composer require joanvt/imagepicker
```

## Required editions

This package has a few improvements respect to the server files provided by ImagePicker. So it means you have to change a few things.
I can't upload the jquery files because it belongs to ImagePicker (get it bro) and I am here to help to integrate this to Laravel and not to steal!

**jquery.imagepicker.js**
```
Line ~411: imagePreview = this.options.url + '?action=preview&file=' + image.name + '&width=800'
```
into this: (just add &path?'+image.path)
````
imagePreview = this.options.url + '?action=preview&file=' + image.name + '&width=800&path='+image.path;
````

## Things to understand

You can try to follow the original documentation from [Hazzardweb](http://docs.hazzardweb.com/imagepicker) But I am introducing new features for a improved architecture for storing images with Laravel Flysystem.

i.e:
`` upload_dir now`` refers to Storage Disk [Laravel Filesystem](https://laravel.com/docs/5.4/filesystem#the-public-disk)

## todo

- wiki
- Create Unit Tests
- More Integrations with other Croppers

## Done

- Uploading Images
- Retrieving Images
- Rotate Images
