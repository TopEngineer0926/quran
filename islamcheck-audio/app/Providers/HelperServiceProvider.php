<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $filesInFolder = \File::files(app_path().'/Helpers');
        foreach($filesInFolder as $path) {

            $file = pathinfo($path);

            require_once( $file['dirname']."/".$file['basename']);

        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
