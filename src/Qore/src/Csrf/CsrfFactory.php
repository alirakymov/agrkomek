<?php

declare(strict_types=1);

namespace Qore\Csrf;

use Psr\Container\ContainerInterface;
use Qore\Csrf\Storage\SessionStorage;
use Qore\SessionManager\SessionManager;

class CsrfFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): CsrfInterface 
    {
        $storage = new SessionStorage($container->get(SessionManager::class));
        return new Csrf($storage);
    }

}
