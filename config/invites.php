<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invite Share URL Pattern
    |--------------------------------------------------------------------------
    |
    | Use {token} as placeholder for the invite token, for example:
    | https://app.example.com/signup?invite={token}
    |
    */
    'share_url_pattern' => env(
        'INVITE_SHARE_URL_PATTERN',
        rtrim((string) env('APP_URL', 'http://localhost'), '/').'/invite/{token}'
    ),
];
