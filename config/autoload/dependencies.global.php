<?php

declare(strict_types=1);

use Mezzio\Csrf\CsrfGuardFactoryInterface;
use Mezzio\Csrf\FlashCsrfGuardFactory;
use Qore\App\Middlewares\TrackingMiddleware;
use Qore\App\Services\SmsService\SmsService;
use Qore\App\Services\SmsService\SmsServiceFactory;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            // Change this to the CsrfGuardFactoryInterface implementation you wish to use:
            CsrfGuardFactoryInterface::class => FlashCsrfGuardFactory::class,
            // Fully\Qualified\ClassOrInterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            TrackingMiddleware::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories'  => [
            // Fully\Qualified\ClassName::class => Fully\Qualified\FactoryName::class,
            SmsService::class => SmsServiceFactory::class,
        ],
    ],
];
