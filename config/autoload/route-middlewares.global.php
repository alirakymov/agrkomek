<?php

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\OAuth2\AuthorizationMiddleware;

return [
    'qore' => [
        'route-middlewares' => [
            '.*' => [],
            '(ApiPrivate)' => [
                AuthenticationMiddleware::class,
            ],
        ]
    ]
];
