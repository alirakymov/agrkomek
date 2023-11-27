<?php

declare(strict_types=1);

namespace Qore\InterfaceGateway;

use Psr\Container\ContainerInterface;

/**
 * Class: InterfaceGatewayFactory
 *
 */
class InterfaceGatewayFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : InterfaceGateway
    {
        return new InterfaceGateway();
    }

}
