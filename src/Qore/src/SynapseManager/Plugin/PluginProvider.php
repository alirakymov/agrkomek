<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin;

use Psr\Container\ContainerInterface;
use Qore\SynapseManager\SynapseManager;

class PluginProvider
{
    private ContainerInterface $container;

    /**
     * @var SynapseManager
     */
    private SynapseManager $sm;


    /**
     * Constuctor
     *
     * @param \Psr\Container\ContainerInterface $_container
     */
    public function __construct(ContainerInterface $_container)
    {
        $this->container = $_container;
    }

    /**
     * Set synapse manager
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm) : void
    {
        $this->sm = $_sm;
    }

    /**
     * Create, initialize and return plugin object
     *
     * @param string $_name
     *
     * @return PluginInterface
     */
    public function get(string $_pluginName) : PluginInterface
    {
        $plugin = $this->container->build($_pluginName);
        $plugin->setSynapseManager($this->sm);
        return $plugin;
    }

}
