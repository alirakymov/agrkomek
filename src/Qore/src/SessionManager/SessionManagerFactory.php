<?php

namespace Qore\SessionManager;

use Laminas\Session\SessionManager as LaminasSessionManager;
use Psr\Container\ContainerInterface;

/**
 * Class: SessionManagerFactory
 *
 */
class SessionManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        return new SessionManager(
            $_container->get(LaminasSessionManager::class)
        );
    }

}

