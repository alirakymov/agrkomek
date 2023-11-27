<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Routes\Executor;

use Qore\Qore;
use Qore\SynapseManager\SynapseManager;
use Psr\Container\ContainerInterface;

class RoutesServiceFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $_container)
    {
        $sm = Qore::service(SynapseManager::class);
        return $sm('Routes:Executor');
    }

}
