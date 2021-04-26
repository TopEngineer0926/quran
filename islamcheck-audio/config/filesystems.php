<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' =>  ('AKIAZ62FVAQR3LVCW3H6'),
            'secret' =>  ('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'),
            'region' =>  'eu-central-1',
            'bucket' =>  ('islamcheck-audio-new'),
            //'url' => env('https://684656493603.signin.aws.amazon.com/console'),
            'credentials' => array(
                'key'    => 'AKIAZ62FVAQR3LVCW3H6',
                'secret' => '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',
                'region' =>  'eu-central-1',
            )
//            'credentials' => [
//
//            ],
        ],

        's3_bulk' => [
            'driver' => 's3',
            'key' =>  ('AKIAZ62FVAQR3LVCW3H6'),
            'secret' =>  ('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'),
            'region' =>  'eu-central-1',
            'bucket' =>  ('quran-audio-import'),

            //'url' => env('https://684656493603.signin.aws.amazon.com/console'),
            'credentials' => array(
                'key'    => 'AKIAZ62FVAQR3LVCW3H6',
                'secret' => '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',
                'region' =>  'eu-central-1',
            )
//            'credentials' => [
//
//            ],
        ],
        'cache' => [
            'store' => 'memcached',
            'expire' => 600,
            'prefix' => 'cache-prefix',
        ],

    ],

];
