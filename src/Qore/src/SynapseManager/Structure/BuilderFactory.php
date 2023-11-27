<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Structure;


use Psr\Container\ContainerInterface;

/**
 * Class: BuilderFactory
 *
 */
class BuilderFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : Builder
    {
        return new Builder($_container);
    }

}
