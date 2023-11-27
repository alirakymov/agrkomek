<?php

declare(strict_types=1);

namespace Qore\QueueManager;

use Qore\Qore;
use Psr\Container\ContainerInterface;
use Qore\QueueManager\Adapter\AmqpAdapter;

class QueueManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @return QueueManager 
     */
    public function __invoke(ContainerInterface $container) : QueueManager
    {
        return new QueueManager();
    }

}

