<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication;

use Psr\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: AuthenticateMiddlewareFactory
 *
 */
class InitializeUserDataMiddlewareFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container):  InitializeUserDataMiddleware
    {
        $ig = $container->get(InterfaceGateway::class);
        return new InitializeUserDataMiddleware(
            $ig(Layout::class, 'layout'),
            $container->get(SynapseManager::class),
            $container->get(UrlHelper::class)
        );
    }

}
