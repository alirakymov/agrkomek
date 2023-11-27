<?php

declare(strict_types=1);

namespace Qore\SynapseManager;


use Qore\EventManager;
use Psr\Container\ContainerInterface;
use Qore\SynapseManager\Plugin\PluginProvider;

/**
 * Class: SynapseFactory
 *
 */
class SynapseManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : SynapseManager
    {
        return new SynapseManager(
            $this->getConfig($_container),
            $_container->get(\Qore\ORM\ModelManager::class),
            $_container->get(Structure\Builder::class),
            $_container->get(EventManager\EventManager::class),
            $_container->get(PluginProvider::class),
            $_container
        );
    }

    /**
     * getConfig
     *
     * @param ContainerInterface $_container
     */
    private function getConfig(ContainerInterface $_container)
    {
        $config = $_container->get('config');
        return $config['synapse-configs'] ?? [];
    }

}
