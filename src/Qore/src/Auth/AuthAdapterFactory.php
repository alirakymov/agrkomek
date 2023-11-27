<?php

namespace Qore\Auth;

use Interop\Container\ContainerInterface;

class AuthAdapterFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthAdapter();
    }
}
