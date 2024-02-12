<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation;

use Psr\Container\ContainerInterface;
use Qore\Config\ConfigContainer;

class OperationPhasesProviderFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): OperationPhasesProviderInterface
    {
        /** @var ConfigContainer */
        $config = $container->get(ConfigContainer::class);

        return new OperationPhasesProvider(
            $config('operation.thread.cache'),
        );
    }

}
