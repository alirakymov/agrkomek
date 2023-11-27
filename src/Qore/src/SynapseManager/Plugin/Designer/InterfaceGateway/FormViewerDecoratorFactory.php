<?php

namespace Qore\SynapseManager\Plugin\Designer\InterfaceGateway;

use Qore\Qore;
use Qore\InterfaceGateway\InterfaceGateway;
use Psr\Container\ContainerInterface;

class FormViewerDecoratorFactory
{
    public function __invoke(ContainerInterface $_container)
    {
        return new FormViewerDecorator(
            $_container->get(InterfaceGateway::class)
        );
    }
}
