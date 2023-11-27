<?php

namespace Qore\CacheManager;

use Laminas\Cache\StorageFactory;
use Laminas\Cache\Storage\Plugin\Serializer;
use Psr\Container\ContainerInterface;

/**
 * Class: CacheManagerFactory
 *
 */
class CacheManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        $adapter = StorageFactory::factory($_container->get('config')['cache'] ?? []);
        $adapter->addPlugin(new Serializer());
        return new CacheManager($adapter);
    }

}

