<?php
/**
 * @see       https://github.com/mezzio/mezzio for the canonical source repository
 * @copyright Copyright (c) 2015-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Qore;

use Psr\Container\ContainerInterface;
use Mezzio\ApplicationPipeline;
use Mezzio\MiddlewareFactory;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;

/**
 * Create an Qore Application instance.
 *
 * This class consumes three other services, and one pseudo-service (service
 * that looks like a class name, but resolves to a different resource):
 *
 * - Mezzio\MiddlewareFactory.
 * - Mezzio\ApplicationPipeline, which should resolve to a
 *   Laminas\Stratigility\MiddlewarePipeInterface instance.
 * - Mezzio\Router\RouteCollector.
 * - Laminas\HttpHandler\RequestHandlerRunner.
 */
class QoreFactory
{
    public function __invoke(ContainerInterface $container) : Qore
    {
        return new Qore(
            $container->get(MiddlewareFactory::class),
            $container->get(ApplicationPipeline::class),
            $container->get(Router\RouteCollector::class),
            $container->get(RequestHandlerRunner::class)
        );
    }
}
