<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication;

use Psr\Container\ContainerInterface;
use Qore\SynapseManager\SynapseManager;
use Webmozart\Assert\Assert;

/**
 * Class: AuthSubjectRepositoryFactory
 *
 */
class AuthSubjectRepositoryFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): AuthSubjectRepository
    {
        return new AuthSubjectRepository($container->get(SynapseManager::class));
    }

}

