<?php

namespace Qore\App\SynapseNodes\Components\User;

use Psr\Container\ContainerInterface;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\SynapseManager;

class UserRepositoryFactory
{
    /**
     * Invoke 
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return UserRepository
     */
    public function __invoke(ContainerInterface $container): UserRepository
    {
        $sm = $container->get(SynapseManager::class);
        $mm = $container->get(ModelManager::class);

        return new UserRepository($mm);
    }
}
