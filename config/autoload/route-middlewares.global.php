<?php

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\OAuth2\AuthorizationMiddleware;
use Qore\App\Middlewares\TrackingMiddleware;
use Qore\App\SynapseNodes\Components\User\ApiPrivate\UserDeviceRegisterMiddleware;

return [
    'qore' => [
        'route-middlewares' => [
            '.*' => [
                TrackingMiddleware::class
            ],
            '(ApiPrivate)' => [
                AuthenticationMiddleware::class,
                UserDeviceRegisterMiddleware::class,
            ],
        ]
    ]
];
