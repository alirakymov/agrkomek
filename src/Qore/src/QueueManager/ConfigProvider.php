<?php

declare(strict_types=1);

namespace Qore\QueueManager;

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
                'factories' => [
                    Command\QueueCreateJob::class => Command\QueueCreateJobFactory::class,
                    Command\QueueInitWorkers::class => Command\QueueInitWorkersFactory::class,
                    Command\QueueWorker::class => Command\QueueWorkerFactory::class,
                    QueueManager::class => QueueManagerFactory::class,
                ]
            ],
            'console' => [
                'commands' => [
                    Command\QueueCreateJob::class,
                    Command\QueueInitWorkers::class,
                    Command\QueueWorker::class,
                ],
            ],
            'qore' => [
                'queue-manager' => [
                    'config_file' => PROJECT_CONFIG_PATH . DS . 'queue.global.php',
                    'jobs' => [],
                    'adapter' => [
                        'class' => Adapter\AmqpAdapter::class,
                        'options' => [
                            'host' => 'localhost',
                            'port' => '5672',
                            'username' => 'guest',
                            'password' => 'guest',
                        ],
                    ]
                ],
            ],
        ];
    }

}
