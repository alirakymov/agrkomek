<?php

declare(strict_types=1);

namespace Qore\Desk;

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
            'qore' => $this->getQoreOptions(),
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
                // - Middlewares: {{{
                Middlewares\BootMiddleware::class,
                Middlewares\BaseUrlMiddleware::class,
                Middlewares\AuthGuardMiddleware::class,
                Middlewares\RoutesMiddleware::class,
                // }}}
                // - Observers: {{{
                Observers\ServiceFileCombiner::class,
                Observers\SynapseCacheCleaner::class,
                // }}}
                // - Actions: {{{
                Actions\Index::class,
                Actions\Login::class,
                Actions\Inspect::class,
                Actions\Services::class,
                Actions\Users::class,
                Actions\Debug::class,
                Actions\Debugger::class,
                Actions\CacheCleaner::class,
                Actions\Synapse\Synapses::class,
                Actions\Synapse\SynapseStructure::class,
                Actions\Synapse\SynapseServiceStructure::class,
                Actions\Synapse\SynapseServiceFormStructure::class,
                // }}}
            ],
            'factories'  => [
            ],
        ];
    }

    /**
     * getORMOptions
     *
     */
    public function getORMOptions() : array
    {
        return [
            'QSystem' => [
                'metadata' => [
                    'tables' => [
                        'Files' => [
                            'entity' => Core\Entities\QSystemBase::class,
                            'columns' => [
                                'iPartner' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => false,
                                ],
                                'processed' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                ],
                                'tsCreated' => [
                                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                                    'null' => false,
                                    'default' => true,
                                ],
                                'tsUpdated' => [
                                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                                    'null' => false,
                                ],
                            ],
                        ],
                    ],
                    'references' => [
                    ]
                ]
            ]
        ];
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
                'app'    => [QORE_FRONT_PATH . DS . implode(DS, ['gateway', 'public', 'templates', 'desk', 'app'])],
                'error'  => [QORE_FRONT_PATH . DS . implode(DS, ['gateway', 'public', 'templates', 'desk', 'error'])],
                'layout' => [QORE_FRONT_PATH . DS . implode(DS, ['gateway', 'public', 'templates', 'desk', 'layout'])],
            ],
        ];
    }

    /**
     * getQoreOptions
     *
     * @return array
     */
    public function getQoreOptions()
    {
        return [
            'desk-path' => '/~desk',
            'desk-routes' => $this->getQoreRoutes(),
        ];
    }

    /**
     * getAppConfigs
     *
     * @return array
     */
    public function getAppConfigs()
    {
        return [];
    }

    /**
     * getQoreRoutes
     *
     * @return array
     */
    private function getQoreRoutes()
    {
        return [
            Actions\Index::class,
            Actions\Login::class,
            Actions\Inspect::class,
            Actions\Debug::class,
            Actions\Users::class,
            Actions\Services::class,
            Actions\Debugger::class,
            Actions\CacheCleaner::class,
            Actions\Synapse\Synapses::class,
            Actions\Synapse\SynapseStructure::class,
            Actions\Synapse\SynapseServiceStructure::class,
            Actions\Synapse\SynapseServiceFormStructure::class,
        ];
    }

    /**
     * getTwigOptions
     *
     */
    public function getTwigOptions() : array
    {
        return [
            'desk-assets_url' => '/static-front.protocol',
        ];
    }

}
