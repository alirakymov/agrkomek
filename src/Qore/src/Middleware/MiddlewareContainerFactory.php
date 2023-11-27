<?php

declare(strict_types=1);

namespace Qore\Middleware;

use Psr\Container\ContainerInterface;

/**
 * Class: MiddlewareContainerFactory
 *
 */
class MiddlewareContainerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        return new MiddlewareContainer($_container);
    }

}
