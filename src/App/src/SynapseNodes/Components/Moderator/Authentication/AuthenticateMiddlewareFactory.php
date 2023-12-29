<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Moderator\Authentication;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Qore\SynapseManager\SynapseManager;

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
        $adapter = $container->get(Adapter\AuthenticationInterface::class);
        Assert::nullOrIsInstanceOfAny($adapter, [ Adapter\AuthenticationInterface::class ]);

        if (null === $adapter) {
            throw new InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new AuthenticateMiddleware($adapter, $container->get(SynapseManager::class));
    }

}
