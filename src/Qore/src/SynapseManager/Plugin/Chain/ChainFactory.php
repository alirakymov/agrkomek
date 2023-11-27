<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Chain;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\Elastic\Elastic;
use Qore\SynapseManager\Plugin\Indexer\Indexer;

/**
 * Class: IndexerFactory
 *
 */
class ChainFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : Chain
    {
        $config = $_container->get('config');
        return new Chain(
            $config['qore']['synapse-configs']['namespaces'] ?? [],
        );
    }

}
