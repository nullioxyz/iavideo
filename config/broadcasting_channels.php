<?php

return [
    'user' => [
        'pattern' => 'user.{userId}',
        'type' => 'private',
        'authorizer' => \App\Domain\Broadcasting\Auth\UserChannelAuthorizer::class,
    ],
];
