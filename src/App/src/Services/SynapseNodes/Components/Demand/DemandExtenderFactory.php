<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Demand;

use Psr\Container\ContainerInterface;

class DemandExtenderFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): DemandExtenderInterface
    {
        return new DemandExtender();
    }

}
