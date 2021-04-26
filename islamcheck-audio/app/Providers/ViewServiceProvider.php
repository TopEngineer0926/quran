<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Enum;
use Carbon\Carbon;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        //////////////////////////////////////////Start Navigation////////////////////////////////////////////////////////
        View::composer('*', function ($view) {
            $nav[0] = [
                'name' => 'Dashboard',
                'icon' => 'clip-home',
                'link' => route('admin.dashboard'),

            ];
            $nav[1] = [
                'name' => 'Surahs',
                'icon' => 'fa fa-upload',
                'link' => route('surahs.index'),
            ];
            $nav[2] = [
                'name' => 'Section',
                'icon' => 'fa fa-upload',
                'link' => route('section.index'),
                'sub' =>
                    [
                        [
                            'name' => 'Section',
                            'icon' => 'fa fa-upload',
                            'link' => route("section.index"),
                        ],
                        [
                            'name' => 'Section Language',
                            'icon' => 'fa fa-upload',
                            'link' => route("section_language.index"),
                        ],

                    ],
            ];
            $nav[3] = [
                'name' => 'Recitations',
                'icon' => 'fa fa-upload',
                'link' => route('recitation.index'),


            ];
//            $nav[4] = [
//                'name' => 'Languages',
//                'icon' => 'fa fa-upload',
//                'link' => route('language.index'),
//                'sub' =>
//                    [
//                        [
//                            'name' => 'Language',
//                            'icon' => 'fa fa-upload',
//                            'link' => route("language.index"),
//                        ],
//                        [
//                            'name' => 'Translated Language',
//                            'icon' => 'fa fa-upload',
//                            'link' => route("translated_language.index"),
//                        ],
//
//                    ],
//
//            ];
            $nav[5] = [
                'name' => 'Reciter',
                'icon' => 'fa fa-upload',
                'link' => route('reciter.index'),
                'sub' =>
                    [
                        [
                            'name' => 'Reciter',
                            'icon' => 'fa fa-upload',
                            'link' => route("reciter.index"),
                        ],
                        [
                            'name' => 'Reciter Language',
                            'icon' => 'fa fa-upload',
                            'link' => route("reciter_language.index"),
                        ],

                    ],
            ];

            $nav[6] = [
                'name' => 'Settings',
                'icon' => 'fa fa-upload',
                'link' => route('setting.index'),

            ];

            $nav[7] = [
                'name' => 'Pending Recitations',
                'icon' => 'fa fa-upload',
                'link' => route('pending-recitation.index'),



            ];

            $view->with('navigation', $nav);
        });
        //////////////////////////////////////////End Navigation///////////////////////////////////////////////////////
    }
}
