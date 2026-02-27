<?php

return [
    'provider' => env('UPLOADS_PROVIDER', env('APP_ENV') === 'production' ? 's3' : 'local'),
    'temp_disk' => env('UPLOADS_TEMP_DISK', 'local'),

    'local' => [
        'media_disk' => env('UPLOADS_LOCAL_MEDIA_DISK', 'public'),
        'media_prefix' => env('UPLOADS_LOCAL_MEDIA_PREFIX', ''),
    ],

    's3' => [
        'media_disk' => env('UPLOADS_S3_MEDIA_DISK', 'spaces'),
        'media_prefix' => env('UPLOADS_S3_MEDIA_PREFIX', env('MEDIA_PREFIX', 'inkai')),
    ],
];
