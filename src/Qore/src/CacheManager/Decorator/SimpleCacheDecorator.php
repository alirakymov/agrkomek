<?php

declare(strict_types=1);

namespace Qore\CacheManager\Decorator;

use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator as LaminasSimpleCacheDecorator;

/**
 * Decorate a laminas-cache storage adapter for usage as a PSR-16 implementation.
 */
class SimpleCacheDecorator extends LaminasSimpleCacheDecorator
{
    /**
     * @param string $_key
     * @param \Closure $_closure
     * @param mixed $_default
     */
    public function __invoke(string $_key, \Closure $_closure, $_default = null)
    {
        return $this->set($_key, $_closure($this->get($_key, $_default)));
    }

}
