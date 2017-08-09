<?php namespace Joanvt\ImagePicker;

use Illuminate\Support\ServiceProvider;

class ImagePickerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {


        $this->publishes([__DIR__.'/../assets' => resource_path()], 'assets');


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