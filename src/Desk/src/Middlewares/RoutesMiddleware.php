<?php

declare(strict_types=1);

namespace Qore\Desk\Middlewares;

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
class RoutesMiddleware extends BaseMiddleware
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
            if ($_router->isRoutesInitialized()) { return; }
            $_router->group(Qore::config('qore.desk-path'), null, function(RouteCollector $_router){
                $_router->registerRoutesFromMiddlewares(Qore::config('qore.desk-routes'));
            });
        });

        return $_handler->handle($_request);
    }

}
