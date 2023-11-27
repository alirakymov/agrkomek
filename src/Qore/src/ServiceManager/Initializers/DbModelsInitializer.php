<?php

namespace Qore\ServiceManager\Initializers;

use Qore\Db\Adapter;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class DbModelsInitializer implements InitializerInterface
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @param mixed $instance
     * @return void
     */
    public function __invoke(ContainerInterface $_container, $_instance)
    {
        $_instance->setAdapter($_container->get(Adapter::class));
    }
}
