<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;


use Qore\ORM\ModelManager;
use Qore\SynapseManager;
use Qore\Collection\Collection;
use Psr\Container\ContainerInterface;

class RepositoryCollectionLoaderFactory
{
    /**
     * __invoke
     *
     * @param \Psr\Container\ContainerInterface $_container
     *
     * @return RepositoryCollectionLoader
     */
    public function __invoke(ContainerInterface $_container) : RepositoryCollectionLoader
    {
        return new RepositoryCollectionLoader($_container);
    }

}
