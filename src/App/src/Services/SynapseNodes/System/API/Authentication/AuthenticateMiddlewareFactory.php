<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * Class: AuthenticateMiddlewareFactory
 *
 */
class AuthenticateMiddlewareFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): AuthenticateMiddleware
    {
        $authentication = $container->get(Adapter\AuthenticationInterface::class);
        Assert::nullOrIsInstanceOfAny($authentication, [ Adapter\AuthenticationInterface::class ]);

        if (null === $authentication) {
            throw new Exception\InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new AuthenticateMiddleware($authentication);
    }

}
