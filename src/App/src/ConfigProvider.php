<?php

declare(strict_types=1);

namespace Qore\App;

use Qore\ORM;
use Qore\Core;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'orm' => $this->getORMOptions(),
            'templates'    => $this->getTemplates(),
            'app' => $this->getAppConfigs(),
            'twig' => $this->getTwigOptions(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
                Middlewares\BootMiddleware::class,
                Middlewares\RoutesMiddleware::class,
                Middlewares\BaseUrlMiddleware::class,
                Middlewares\AuthGuardMiddleware::class,
                Middlewares\CsrfGuardMiddleware::class,
                Middlewares\NotifySubscriberMiddleware::class,
                Observers\FrontAppCombiner::class,
                Actions\ManagerIndex::class,
                Actions\Login::class,
            ],
            'factories'  => [
                Services\Amadeus\Amadeus::class => Services\Amadeus\AmadeusFactory::class,
                Services\Payment\Paybox::class => Services\Payment\PayboxFactory::class,
                Services\QRCode\QRGenerator::class => Services\QRCode\QRGeneratorFactory::class,
                Services\UserStack\UserStackInterface::class => Services\UserStack\UserStackFactory::class,
                SynapseNodes\System\Users\Authentication\Adapter::class => SynapseNodes\System\Users\Authentication\AdapterFactory::class,
                SynapseNodes\System\Routes\Api\RoutesService::class => SynapseNodes\System\Routes\Api\RoutesServiceFactory::class,
                SynapseNodes\System\Routes\Executor\RoutesService::class => SynapseNodes\System\Routes\Executor\RoutesServiceFactory::class,
                SynapseNodes\System\Routes\Manager\RoutesService::class => SynapseNodes\System\Routes\Manager\RoutesServiceFactory::class,
                SynapseNodes\System\Users\Authentication\AuthenticationService::class => SynapseNodes\System\Users\Authentication\AuthenticationServiceFactory::class,
            ],
            'initializer-targets' => [
            ]
        ];
    }

    /**
     * getORMOptions
     *
     */
    public function getORMOptions() : array
    {
        return [];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'frontapp' => [touch_dir([PROJECT_PATH, 'public', 'templates'])],
            ],
        ];
    }

    /**
     * getAppConfigs
     *
     * @return array
     */
    public function getAppConfigs()
    {
        return [
            'routes' => $this->getAppRoutes(),
            'cmf' => [
                'admin-path' => '/~admin',
                'routes' => [
                    Actions\ManagerIndex::class,
                    Actions\Login::class,
                ],
                'templates' => [
                    'cache-file' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'synapse-system']) . DS . 'templates'
                ]
            ],
            'upload-paths' => [
                'global' => [
                    'public' => [
                        'images' => [
                            'path' => touch_dir(PROJECT_PATH . '/../global.static/public/uploads/images'),
                            'uri' => '/global-images/{location}/{uniqid}'
                        ],
                        'files' => [
                            'path' => touch_dir(PROJECT_PATH . '/../global.static/public/uploads/files'),
                            'uri' => '/global-files/{location}/{uniqid}'
                        ],
                    ],
                ],
                'local' => [
                    'public' => [
                        'images' => [
                            'path' => touch_dir(PROJECT_PATH . '/public/uploads/images'),
                            'uri' => '/images/{location}/{uniqid}'
                        ],
                        'files' => [
                            'path' => touch_dir(PROJECT_PATH . '/public/uploads/files'),
                            'uri' => '/files/{location}/{uniqid}'
                        ],
                    ]
                ]
            ],
        ];
    }

    /**
     * getAppRoutes
     *
     */
    private function getAppRoutes()
    {
        return [];
    }

    /**
     * getTwigOptions
     *
     */
    public function getTwigOptions() : array
    {
        return [
            'loaders' => [
            ]
        ];
    }

}
