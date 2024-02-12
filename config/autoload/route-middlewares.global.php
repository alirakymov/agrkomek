<?php

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\OAuth2\AuthorizationMiddleware;
use Qore\App\Middlewares\TrackingMiddleware;

return [
    'qore' => [
        'route-middlewares' => [
            '.*' => [
                TrackingMiddleware::class
            ],
            '(ApiPrivate)' => [
                AuthenticationMiddleware::class,
            ],
        ]
    ]
];
