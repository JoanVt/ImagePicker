<?php namespace Joanvt\ImagePicker;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class ImagePickerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        if(!File::exists(config_path('imagepicker.php'))){
            $this->publishes([__DIR__.'/../config' => config_path()], 'config');
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');


    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('ImagePicker', \Joanvt\ImagePicker\ImagePicker::class);

        $this->app->bind('Joanvt\ImagePicker\Traits\ImagePicker', function(){

            return new ImagePicker();

        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}