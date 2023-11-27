<?php

namespace Qore\EventManager;

use Psr\Container\ContainerInterface;

/**
 * Class: EventManagerFactory
 *
 */
class EventManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        return new EventManager();
    }

}
