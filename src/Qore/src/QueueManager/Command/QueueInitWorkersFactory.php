<?php

declare(strict_types=1);

namespace Qore\QueueManager\Command;

use Qore\Qore;
use Psr\Container\ContainerInterface;
use Qore\QueueManager\QueueManager;

class QueueInitWorkersFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @return QueueInitWorkers 
     */
    public function __invoke(ContainerInterface $container) : QueueInitWorkers
    {
        $command = new QueueInitWorkers();
        $command->setConfig(Qore::config('qore.queue-manager', []));
        $command->setQueueManager($container->get(QueueManager::class));

        return $command;
    }

}

