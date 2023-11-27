<?php

declare(strict_types=1);

namespace Qore\Router;

use Qore\Qore;
use Mezzio\Router\FastRouteRouter as MezzioFastRouteRouter;

/**
 * Class: FastRouteRouter
 *
 * @see MezzioFastRouteRouter
 */
class FastRouteRouter extends MezzioFastRouteRouter
{
    /**
     * routesCacheFile
     *
     * @var mixed
     */
    private $routesCacheFile = null;
    /**
     * unserialized
     *
     * @var mixed
     */
    private $unserialized = false;

    /**
     * proxyRoutes
     *
     * @var mixed
     */
    private $proxyRoutes = [];

    /**
     * isUnserialized
     *
     */
    public function isUnserialized()
    {
        return $this->unserialized;
    }

    /**
     * unzip
     *
     * @param string $_routesFile
     */
    public function unzip(string $_routesFile)
    {
        $this->routesCacheFile = $_routesFile;
        if (file_exists($_routesFile)) {
            $reflection = new \ReflectionClass(MezzioFastRouteRouter::class);
            $reflectionPropertyRoutes = $reflection->getProperty('routes');
            $reflectionPropertyRoutes->setAccessible(true);
            $reflectionPropertyRoutes->setValue($this, $this->proxyRoutes = unserialize(file_get_contents($this->routesCacheFile)));
            $this->unserialized = true;
        }
    }

    /**
     * getProxyRoutes
     *
     */
    public function getProxyRoutes()
    {
        return $this->proxyRoutes;
    }

    /**
     * __destruct
     *
     */
    public function __destruct()
    {
        if (! $this->isUnserialized() && ! is_null($this->routesCacheFile)) {
            $reflection = new \ReflectionClass(MezzioFastRouteRouter::class);
            $reflectionPropertyRoutes = $reflection->getProperty('routes');
            $reflectionPropertyRoutes->setAccessible(true);
            file_put_contents($this->routesCacheFile, serialize($reflectionPropertyRoutes->getValue($this)));
        }
    }

}
