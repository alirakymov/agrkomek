<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;


use Qore\SynapseManager;
use Psr\Container\ContainerInterface;

/**
 * Class: SynapseFactory
 *
 */
class RepositoryFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     * @param string $_requestedName
     */
    public function __invoke(ContainerInterface $_container, string $_requestedName) : RepositoryInterface
    {
        $options = [];
        $repository = new $_requestedName(...$options);
        $repository->setSynapseManager($_container->get(SynapseManager\SynapseManager::class));

        return $repository;
    }

}
