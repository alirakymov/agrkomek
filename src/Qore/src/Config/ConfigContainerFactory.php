<?php

declare(strict_types=1);

namespace Qore\Config;

use Psr\Container\ContainerInterface;

class ConfigContainerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : ConfigContainer
    {
        return new ConfigContainer($_container->get('config'));
    }

}
