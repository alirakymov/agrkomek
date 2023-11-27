<?php

declare(strict_types=1);

namespace Qore\Daemon\Supervisor;

use Psr\Container\ContainerInterface;
use Zend;
use Supervisor\Configuration\Configuration;
use Supervisor\Configuration\Section\Supervisord;
use Supervisor\Configuration\Section\Program;
use Supervisor\Supervisor;
use Supervisor\Connector;
use Indigo\Ini\Renderer;

class SupervisorConfiguratorFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : SupervisorConfigurator
    {
        return new SupervisorConfigurator();
    }
}

