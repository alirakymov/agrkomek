<?php

declare(strict_types=1);

namespace Qore\Daemon\Supervisor;

use Qore\Qore;
use Laminas\XmlRpc\Client;
use Supervisor\Supervisor;
use Supervisor\Connector;
use Psr\Container\ContainerInterface;

/**
 * Class: SupervisorFactory
 *
 */
class SupervisorFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : Supervisor
    {
        return new Supervisor(
            new Connector\Zend(
                new Client(Qore::config('qore.daemons.supervisor.uri'))
            )
        );
    }

}

