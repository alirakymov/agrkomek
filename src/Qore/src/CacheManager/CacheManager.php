<?php

declare(strict_types=1);

namespace Qore\CacheManager;

use Qore\CacheManager\Decorator\SimpleCacheDecorator;

/**
 * Class: CacheManager
 *
 */
class CacheManager
{
    /**
     * adapter
     *
     * @var mixed
     */
    protected $adapter = null;

    /**
     * __construct
     *
     * @param string $_cacheFolder
     */
    public function __construct($_adapter)
    {
        $this->adapter = $_adapter;
    }

    /**
     * __invoke
     *
     * @param string $_namespace
     */
    public function __invoke(string $_namespace = '')
    {
        return new SimpleCacheDecorator((clone $this->adapter)
            ->setOptions((clone $this->adapter->getOptions())->setNamespace($_namespace)));
    }

}
