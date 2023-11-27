<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Decorator;


use Qore\SynapseManager;
use Qore\Collection\Collection;
use Psr\Container\ContainerInterface;

/**
 * Class: TileComponentFactory
 *
 */
class ListComponentFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     * @param string $_requestedName
     */
    public function __invoke(ContainerInterface $_container, string $_requestedName) : ListComponent
    {
        return new ListComponent();
    }

}
