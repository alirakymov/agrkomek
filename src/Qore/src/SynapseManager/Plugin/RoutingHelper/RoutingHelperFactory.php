<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\RoutingHelper;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\Elastic\Elastic;
use Qore\SynapseManager\Plugin\Indexer\Indexer;

/**
 * Class: IndexerFactory
 *
 */
class RoutingHelperFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : RoutingHelper
    {
        return new RoutingHelper();
    }

}
