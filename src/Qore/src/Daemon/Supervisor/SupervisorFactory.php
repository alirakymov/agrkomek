<?php

declare(strict_types=1);

namespace Qore\Daemon\Supervisor;

use GuzzleHttp\Client as GuzzleHttpClient;
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
        // Create Guzzle HTTP client
        $guzzleClient = new GuzzleHttpClient();

        // Pass the url and the guzzle client to the fXmlRpc Client
        $client = new \fXmlRpc\Client(
            Qore::config('qore.daemons.supervisor.uri'),
            new \fXmlRpc\Transport\PsrTransport(
                new \GuzzleHttp\Psr7\HttpFactory(),
                $guzzleClient
            )
        );

        return new Supervisor($client);
    }

}

