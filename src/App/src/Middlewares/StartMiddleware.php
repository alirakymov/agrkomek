<?php

namespace Qore\App\Middlewares;

use Mezzio\Router\RouteResult;
use Qore\Qore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class: StartMiddleware
 *
 * @see BaseMiddleware
 */
class StartMiddleware implements MiddlewareInterface
{
    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $routeResult = $_request->getAttribute(RouteResult::class);
        $routeOptions = $routeResult->getMatchedRoute()->getOptions();
        $middleware = $routeOptions['proxing_middleware'];
        # - Register pipes
        $pipeline = Qore::pipeline([
            Qore::service(CsrfGuardMiddleware::class),
            Qore::service(AuthGuardMiddleware::class),
            Qore::service($middleware),
        ]);

        return $pipeline->process($_request, $_handler);
    }

}
