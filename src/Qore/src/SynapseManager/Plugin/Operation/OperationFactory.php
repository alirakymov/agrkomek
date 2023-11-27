<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Operation;

use Psr\Container\ContainerInterface;
use Qore\CacheManager\CacheManager;
use Qore\Config\ConfigContainer;
use Qore\DealingManager\DealingManager;
use Qore\Lock\Lock;
use Qore\NotifyManager\NotifyManager;
use Qore\QueueManager\QueueManager;

class OperationFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : Operation
    {
        $cm = $_container->get(CacheManager::class);
        $config = $_container->get(ConfigContainer::class);
        $config = $config->wrap($config('qore.synapse-configs.operation', []), [
            'debug' => $config('debug', false),
        ]);

        return new Operation(
            $_container->get(DealingManager::class),
            $_container->get(QueueManager::class),
            $_container->get(NotifyManager::class),
            $_container->get(Lock::class),
            $cm(preg_replace('/\W+/', '-', sprintf('%s-%s', basename(PROJECT_PATH), Operation::class))),
            $config
        );
    }

}
