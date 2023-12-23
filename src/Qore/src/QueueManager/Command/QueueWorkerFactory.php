<?php

declare(strict_types=1);

namespace Qore\QueueManager\Command;

use Qore\Qore;
use Psr\Container\ContainerInterface;
use Qore\QueueManager\QueueManager;

class QueueWorkerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : QueueWorker
    {
        $command = new QueueWorker();
        $command->setConfig(Qore::config('qore.queue-manager', []));
        $command->setQueueManager($container->get(QueueManager::class));

        return $command;
    }

}

