<?php

namespace Qore\SessionManager;

use Psr\Container\ContainerInterface;

/**
 * Class: SessionMiddlewareFactory
 *
 */
class SessionMiddlewareFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        return new SessionMiddleware(
            $_container->get(SessionManager::class)
        );
    }

}

