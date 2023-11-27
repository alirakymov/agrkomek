<?php

namespace Qore\Form\Protector;

use Qore\Qore;
use Psr\Container\ContainerInterface;
use Qore\CacheManager\CacheManager;
use Qore\SessionManager\SessionManager;

class TightnessProtectorFactory
{
    public function __invoke(ContainerInterface $_container): TightnessProtector
    {
        return new TightnessProtector($_container->get(SessionManager::class));
    }
}
