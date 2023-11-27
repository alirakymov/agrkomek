<?php

declare(strict_types=1);

namespace Qore\NotifyManager;

use Qore\Qore;
use Psr\Container\ContainerInterface;

class NotifyManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : NotifyManager
    {
        return new NotifyManager();
    }

}

