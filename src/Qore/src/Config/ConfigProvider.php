<?php

declare(strict_types=1);

namespace Qore\Config;

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
        return array_merge(
            [
                'dependencies' => $this->getDependencies(),
            ]
        );
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [],
            'aliases' => [],
            'abstract_factories' => [],
            'factories' => [
                ConfigContainer::class => ConfigContainerFactory::class,
            ],
            'shared' => [
            ],
            'initializers' => [],
            'initializer-targets' => [],
        ];
    }

}
