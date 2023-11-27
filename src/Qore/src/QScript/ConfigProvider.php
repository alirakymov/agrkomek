<?php

declare(strict_types=1);

namespace Qore\QScript;

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
            'dependencies' => [
                'aliases' => [
                    QScript::class => QScriptInterface::class,
                ],
                'factories' => [
                    QScriptInterface::class => QScriptFactory::class
                ]
            ],
        ];
    }

}
