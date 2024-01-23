<?php

namespace Qore\Manticore;

use Psr\Container\ContainerInterface;
use Qore\Manticore\ManticoreClientBuilder;

class ManticoreFactory
{
    /**
     * Create client instance for elasticsearch service
     *
     * @param \Psr\Container\ContainerInterface $_container
     *
     * @return
     */
    public function __invoke(ContainerInterface $_container): ManticoreInterface
    {
        $config = $_container->get('config');
        $config = array_merge(
            ['host' => '127.0.0.1','port' => 9308],
            $config['manticore']['adapter'] ?? [],
        );

        return new Manticore($config);
    }

}
