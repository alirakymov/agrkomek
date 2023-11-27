<?php

declare(strict_types=1);

namespace Qore\ORM;

use Qore\Qore;
use Qore\EventManager;
use Qore\Database\Adapter\Adapter;
use Psr\Container\ContainerInterface;

class ModelManagerFactory
{
    public function __invoke(ContainerInterface $_container) : ModelManager
    {
        return new ModelManager(
            Qore::service(Adapter::class),
            Qore::service(Gateway\Provider::class),
            Qore::service(Entity\Provider::class),
            Qore::service(Mapper\Provider::class),
            Qore::service(EventManager\EventManager::class)
        );
    }
}
