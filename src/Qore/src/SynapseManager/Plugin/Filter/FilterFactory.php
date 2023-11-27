<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\Form\FormManager;

/**
 * Class: Filter factory 
 *
 */
class FilterFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     * @return FilterInterface 
     */
    public function __invoke(ContainerInterface $_container) : FilterInterface
    {
        $config = $_container->get('config');
        return new Filter(
            $_container->get(DealingManager::class),
            $_container->get(FormManager::class),
            $config['qore']['synapse-configs']['namespaces'] ?? []
        );
    }

}
