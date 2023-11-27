<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\Elastic\Elastic;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\SynapseManager\Plugin\Indexer\Indexer;

class DesignerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : Designer
    {
        $config = $_container->get('config');
        return new Designer(
            $_container->get(DealingManager::class),
            $_container->get(InterfaceGateway::class)
        );
    }

}
