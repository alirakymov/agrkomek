<?php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\ProjectConfiguration;
use Qore\Cors\Configuration\ProjectConfigurationFactory;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => [ConfigurationInterface::ANY_ORIGIN], // Allow any origin
        'allowed_headers' => [], // No custom headers allowed
        'allowed_max_age' => '600', // 10 minutes
        'allowed_methods' => ['GET', 'POST'], // Allow cookies
        'credentials_allowed' => true, // Allow cookies
        'exposed_headers' => ['X-Custom-Header'], // Tell client that the API will always return this header
    ],
    'dependencies' => [
        'factories'  => [
            ProjectConfiguration::class => ProjectConfigurationFactory::class
        ],
    ],
];
