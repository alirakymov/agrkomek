<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication;

use Psr\Container\ContainerInterface;
use Qore\ORM\ModelManager;

/**
 * Class: AuthSubjectRepositoryFactory
 *
 */
class UserRepositoryFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): UserRepository
    {
        return new UserRepository($container->get(ModelManager::class));
    }

}

