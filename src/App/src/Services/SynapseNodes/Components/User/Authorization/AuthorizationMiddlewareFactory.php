<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authorization;

use Psr\Container\ContainerInterface;

/**
 * Class: AuthenticateMiddlewareFactory
 *
 */
class AuthorizationMiddlewareFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): AuthorizationMiddleware
    {
        return new AuthorizationMiddleware();
    }

}
