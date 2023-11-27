<?php

namespace Qore\Form\Decorator;

use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Psr\Container\ContainerInterface;

class QoreFrontFactory
{
    public function __invoke(ContainerInterface $_container)
    {
        return new QoreFront(
            $_container->get(InterfaceGateway::class)
        );
    }
}
