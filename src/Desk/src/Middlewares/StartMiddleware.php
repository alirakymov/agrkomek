<?php

namespace Qore\Desk\Middlewares;

use Mezzio\Router\RouteResult;
use Qore\Qore;
use Qore\Middleware\BaseMiddleware;
use Qore\Router\RouteCollector;
use Mezzio\Twig\TwigExtension;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class: StartMiddleware
 *
 * @see BaseMiddleware
 */
class StartMiddleware extends BaseMiddleware
{
    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        # - Set assets url
        Qore::service(TwigExtension::class)->setAssetsUrl(
            Qore::config('twig.desk-assets_url')
        );

        $routeResult = $_request->getAttribute(RouteResult::class);
        $routeOptions = $routeResult->getMatchedRoute()->getOptions();
        $middleware = $routeOptions['proxing_middleware'];

        if (is_string($middleware)) {
            $middleware = Qore::service($middleware);
        }

        # - Register pipeline
        $pipeline = Qore::pipeline([
            Qore::service(AuthGuardMiddleware::class),
            $middleware,
        ]);

        return $pipeline->process($_request, $_handler);
    }
}
