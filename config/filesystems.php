<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Profile Picture Disk
    |--------------------------------------------------------------------------
    |
    | Keep avatar uploads on one consistent disk. When a Laravel Cloud object
    | storage bucket is attached, AWS_BUCKET is provided and avatars are stored
    | on the S3-compatible disk. Local development continues to use the public
    | disk. AVATAR_FILESYSTEM_DISK may be set to override this behaviour.
    |
    */
    'avatar_disk' => env(
        'AVATAR_FILESYSTEM_DISK',
        env('AWS_BUCKET') ? 's3' : 'public'
    ),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'auto'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
