<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin;

use Psr\Container\ContainerInterface;

/**
 * Class: SynapseFactory
 *
 */
class PluginProviderFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : PluginProvider
    {
        return new PluginProvider($_container);
    }

}
