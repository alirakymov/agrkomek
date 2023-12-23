<?php

declare(strict_types=1);

namespace Qore\QueueManager\Command;

use Qore\Qore;
use Psr\Container\ContainerInterface;

class QueueCreateJobFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @return QueueCreateJob 
     */
    public function __invoke(ContainerInterface $container) : QueueCreateJob
    {
        $command = new QueueCreateJob();
        $command->setConfig(Qore::config('qore.queue-manager', []));
        return $command;
    }

}

