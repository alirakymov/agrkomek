<?php

declare(strict_types=1);

namespace Qore\Csrf;


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
            'aliases' => [
                Csrf::class => CsrfInterface::class,
            ],
            'abstract_factories' => [],
            'factories' => [
                CsrfInterface::class => CsrfFactory::class,
                CsrfMiddleware::class => CsrfMiddlewareFactory::class,
            ],
            'shared' => [],
            'initializers' => [],
            'initializer-targets' => [],
        ];
    }

}
