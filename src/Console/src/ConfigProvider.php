<?php

declare(strict_types=1);

namespace Qore\Console;

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
                'invokables' => [
                    \Qore\Console\Commands\Command::class,
                    \Qore\Console\Commands\FrontBuilder::class,
                ],
                'factories' => [
                    Commands\DumpServer::class => Commands\DumpServerFactory::class,
                ]
            ],
            'console' => [
                'commands' => [
                    \Qore\Console\Commands\Command::class,
                    \Qore\Console\Commands\DumpServer::class,
                    \Qore\Console\Commands\FrontBuilder::class,
                ],
            ],
            'qore' => [
                'prefixes' => [
                    'database' => 'qore_'
                ],
                'paths' => [
                    'console_config_file' => PROJECT_CONFIG_PATH . DS . 'console.global.php',
                ],
            ],
            'dump-server' => [
                'host' => '127.0.0.1:9912',
                'client' => '127.0.0.1:9912',
                'html-file' => touch_dir([PROJECT_PATH, 'storage', 'console', 'debug']) . DS . 'var-dumper.html',
            ],
        ];
    }

}
