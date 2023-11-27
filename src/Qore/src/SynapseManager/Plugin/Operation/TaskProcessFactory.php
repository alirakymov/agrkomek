<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Operation;

use Psr\Container\ContainerInterface;
use Qore\CacheManager\CacheManager;
use Qore\Config\ConfigContainer;
use Qore\NotifyManager\NotifyManager;
use Qore\QueueManager\QueueManager;
use Qore\SynapseManager\SynapseManager;

class TaskProcessFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : TaskProcess
    {
        $cm = $_container->get(CacheManager::class);
        $config = $_container->get(ConfigContainer::class);
        $config = $config->wrap($config('qore.synapse-configs.operation', []), [
            'debug' => $config('debug', false),
        ]);

        return new TaskProcess(
            null,
            $_container->get(SynapseManager::class),
            $_container->get(QueueManager::class),
            $_container->get(NotifyManager::class),
            $cm(preg_replace('/\W+/', '-', sprintf('%s-%s', basename(PROJECT_PATH), Operation::class))),
            $config
        );
    }

}
