<?php

declare(strict_types=1);

namespace Qore\App\Middlewares;

use Qore\Qore;
use Qore\Middleware\BaseMiddleware;
use Qore\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class: RoutesMiddleware
 *
 * @see BaseMiddleware
 */
class RoutesMiddleware implements MiddlewareInterface
{
    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        Qore::service(RouteCollector::class)->proxyRoutes(StartMiddleware::class, function(RouteCollector $_router) {
            # - If routes is cached
            if ($_router->isRoutesInitialized()) { 
                return; 
            }

            Qore::measure('Routes of SynapseSystem', function() use ($_router) {
                # - Get synapse service
                $sm = Qore::service(\Qore\SynapseManager\SynapseManager::class);
                # - Register App routes of SynapseRoutes:Executor
                if ($cmfRoutesManager = $sm('Routes:Api')) {
                    $_router->group('/api', null, fn ($_router) => $sm('Routes:Api')->routes($_router));
                }
                # - Register App routes of SynapseRoutes:Executor
                if ($cmfRoutesManager = $sm('Routes:Executor')) {
                    $sm('Routes:Executor')->routes($_router);
                }
                # - $_router->registerRoutesFromMiddlewares(Qore::config('app.routes'));
                $_router->group(Qore::config('app.cmf.admin-path'), null, function(RouteCollector $_router) use ($sm) {
                    $_router->registerRoutesFromMiddlewares(Qore::config('app.cmf.routes'));
                    if ($cmfRoutesManager = $sm('Routes:Manager')) {
                        $cmfRoutesManager->routes($_router);
                    }
                });
            });
        });
        return $_handler->handle($_request);
    }

}
