<?php

declare(strict_types=1);

namespace Qore\Auth;

use Laminas\Authentication\Storage\Session;
use Interop\Container\ContainerInterface;
use Qore\SessionManager\SessionManager;

/**
 * Class: AuthenticationServiceFactory
 *
 */
class AuthenticationServiceFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $_container)
    {
        $storage = new Session(
            null,
            str_replace('\\', '_', AuthenticationService::class),
            $_container->get(SessionManager::class)->getManager()
        );

        return new AuthenticationService($storage, $_container->get(AuthAdapter::class));
    }

}
