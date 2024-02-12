<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation;

use Psr\Container\ContainerInterface;
use Qore\Config\ConfigContainer;
use Qore\QScript\QScript;

class OperationConstructorFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): OperationConstructorInterface 
    {
        /** @var ConfigContainer */
        $config = $container->get(ConfigContainer::class);

        return new OperationConstructor(
            $config('operation.thread.bootstrap'),
            $config('operation.thread.cache'),
            $container->get(QScript::class)
        );
    }

}
