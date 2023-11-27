<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper;

use Qore\Qore;
use Psr\Container\ContainerInterface;

class ProviderFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : Provider
    {
        $provider = new Provider();

        foreach (Qore::config('orm', []) as $namespace => $metadata) {
            $provider->registerMapper(new Mapper($namespace, new Driver\ArrayDriver($metadata)));
        }

        return $provider;
    }
}
