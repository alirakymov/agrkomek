<?php

declare(strict_types=1);

namespace Qore\Csrf;

use Psr\Container\ContainerInterface;

class CsrfMiddlewareFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): CsrfMiddleware
    {
        return new CsrfMiddleware($container->get(CsrfInterface::class));
    }

}
