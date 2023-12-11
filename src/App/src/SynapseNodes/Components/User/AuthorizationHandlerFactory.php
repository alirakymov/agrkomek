<?php

namespace Qore\App\SynapseNodes\Components\User;

use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\Psr17ResponseFactoryTrait;
use Psr\Container\ContainerInterface;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\SynapseManager;

class AuthorizationHandlerFactory
{
    use Psr17ResponseFactoryTrait;

    /**
     * invoke 
     *
     * @param \Psr\Container\ContainerInterface $container 
     *
     * @return AuthorizationHandler 
     */
    public function __invoke(ContainerInterface $container): AuthorizationHandler
    {
        return new AuthorizationHandler(
            $container->get(AuthorizationServer::class),
            $this->detectResponseFactory($container)
        );
    }
}
