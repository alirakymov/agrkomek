<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Psr\Container\ContainerInterface;
use Qore\Manticore\ManticoreInterface;

/**
 * Class: IndexerFactory
 *
 */
class SearchEngineFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : SearchEngineInterface
    {
        return new SearchEngine(
            $_container->get(ManticoreInterface::class)
        );

    }

    /**
     * [TODO:description]
     *
     * @return [TODO:type] [TODO:description]
     */
    public function something(): void
    {

    }

}
