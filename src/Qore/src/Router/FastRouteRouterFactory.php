<?php

declare(strict_types=1);

namespace Qore\Router;

use Qore\Qore;
use Qore\SynapseManager\SynapseManager;
// use function Opis\Closure\{serialize, unserialize};
use Psr\Container\ContainerInterface;

/**
 * Create and return an instance of FastRouteRouter.
 *
 * Configuration should look like the following:
 *
 * <code>
 * 'router' => [
 *     'fastroute' => [
 *         'cache_enabled' => true, // true|false
 *         'cache_file'   => '(/absolute/)path/to/cache/file', // optional
 *         'routes_cache_file'   => '(/absolute/)path/to/cache/file', // optional
 *     ],
 * ]
 * </code>
 */
class FastRouteRouterFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $_container)
    {
        $routerCacheEnabled = Qore::config('router.fastroute.cache_enabled', false);
        $routesFile = Qore::config('router.fastroute.routes_cache_file', null);

        $router = $this->createRouter($_container);

        if ($routerCacheEnabled && ! is_null($routesFile)
            # - Temporarily hard coded: https://github.com/mezzio/mezzio-fastroute/issues/4
            && ! is_null($dispatchDataFile = Qore::config('router.fastroute.cache_file', null)) && is_file($dispatchDataFile)
        ) {
            $router->unzip($routesFile);
        }

        return $router;
    }

    /**
     * createRouter
     *
     */
    private function createRouter(ContainerInterface $_container)
    {
        $config = $_container->has('config')
            ? $_container->get('config')
            : [];

        $config = $config['router']['fastroute'] ?? [];

        return new FastRouteRouter(null, null, $config);
    }

}
