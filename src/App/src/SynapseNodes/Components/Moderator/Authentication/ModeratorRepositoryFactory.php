<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Moderator\Authentication;

use Psr\Container\ContainerInterface;
use Qore\ORM\ModelManager;

/**
 * Class: AuthSubjectRepositoryFactory
 *
 */
class ModeratorRepositoryFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): ModeratorRepository 
    {
        return new ModeratorRepository($container->get(ModelManager::class));
    }

}

