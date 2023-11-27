<?php

declare(strict_types=1);

namespace Qore\Router;

use Psr\Container\ContainerInterface;
use Mezzio\Router as ZERouter;

class RouteCollectorFactory
{
    /**
     * @throws Exception\MissingDependencyException if the RouterInterface service is
     *     missing.
     */
    public function __invoke(ContainerInterface $container) : RouteCollector
    {
        if (! $container->has(ZERouter\RouterInterface::class)) {
            throw ZERouter\Exception\MissingDependencyException::dependencyForService(
                ZERouter\RouterInterface::class,
                RouteCollector::class
            );
        }

        return new RouteCollector($container->get(ZERouter\RouterInterface::class));
    }
}
