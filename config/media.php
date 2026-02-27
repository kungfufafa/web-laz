<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Local Media Paths
    |--------------------------------------------------------------------------
    |
    | Paths on the local disk that are allowed to be served through
    | the /api/media endpoint. Comma separated in env.
    |
    */

    'allowed_local_paths' => array_values(array_filter(array_map(
        static fn (string $path): string => trim($path),
        explode(',', (string) env('MEDIA_ALLOWED_LOCAL_PATHS', 'avatars,articles,videos'))
    ))),

];
