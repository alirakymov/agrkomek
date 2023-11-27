<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\Manticore\Manticore;

/**
 * Class: IndexerFactory
 *
 */
class IndexerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : Indexer
    {
        $config = $_container->get('config');
        return new Indexer(
            $_container->get(DealingManager::class),
            $_container->get(SearchEngineInterface::class),
            $config['qore']['synapse-configs']['namespaces'] ?? [],
            $config['qore']['synapse-configs']['indexer'] ?? [],
        );
    }

}
