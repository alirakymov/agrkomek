<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;


use Qore\ORM\ModelManager;
use Qore\SynapseManager;
use Qore\Collection\Collection;
use Psr\Container\ContainerInterface;

/**
 * Class: SynapseFactory
 *
 */
class RepositoryFactory
{
    /**
     * servicesCollection
     *
     * @var mixed
     */
    private $servicesCollection = null;

    /**
     * formsCollection
     *
     * @var mixed
     */
    private $formsCollection = null;

    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     * @param string $_requestedName
     */
    public function __invoke(ContainerInterface $_container, string $_requestedName) : RepositoryInterface
    {
        switch (true) {
            case $_requestedName === Service\Repository::class:
                return $this->createServiceArtificerRepository($_container);
            case $_requestedName === Form\Repository::class:
                return $this->createFormArtificerRepository($_container);
        }
    }

    /**
     * createServiceArtificerRepository
     *
     * @param ContainerInterface $_container
     */
    private function createServiceArtificerRepository(ContainerInterface $_container)
    {
        $repositoryCollectionLoader = $_container->get(RepositoryCollectionLoader::class);
        return new Service\Repository($repositoryCollectionLoader->getCollectionOfServiceRepository());
    }

    /**
     * createFormArtificerRepository
     *
     * @param ContainerInterface $_container
     */
    private function createFormArtificerRepository(ContainerInterface $_container)
    {
        $repositoryCollectionLoader = $_container->get(RepositoryCollectionLoader::class);
        return new Form\Repository($repositoryCollectionLoader->getCollectionOfFormRepository());
    }

}
