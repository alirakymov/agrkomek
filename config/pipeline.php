<?php

declare(strict_types=1);

use Mezzio\Flash\FlashMessageMiddleware;
use Psr\Container\ContainerInterface;
use Qore\Qore as Qore;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Session\SessionMiddleware as MezzioSessionMiddleware;
use Qore\Csrf\CsrfMiddleware;
use Qore\SessionManager\SessionMiddleware;

/**
 * Setup middleware pipeline:
 */
return function (Qore $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    # The error handler should be the first (most outer) middleware to catch all Exceptions.
    $app->pipe(ErrorHandler::class);
    # - Cors Middleware Fix preflight requests
    $app->pipe(CorsMiddleware::class);
    # - Parse body params
    $app->pipe(BodyParamsMiddleware::class);
    # - Register qore-session manager
    $app->pipe(SessionMiddleware::class);
    # - Csrf initializer middleware
    $app->pipe(CsrfMiddleware::class);

    $app->pipe(ServerUrlMiddleware::class);
    # - Qore system boot middleware
    $app->pipe(\Qore\Desk\Middlewares\BootMiddleware::class);
    # - Boot Middleware
    $app->pipe(\Qore\App\Middlewares\BootMiddleware::class);
    # - Register routes of Qore Desk application
    $app->pipe(\Qore\Desk\Middlewares\RoutesMiddleware::class);
    # - Register routes of Main application
    $app->pipe(\Qore\App\Middlewares\RoutesMiddleware::class);

    # Pipe more middleware here that you want to execute on every request:
    # - bootstrapping
    # - pre-conditions
    # - modifications to outgoing responses
    #
    # Piped Middleware may be either callables or service names. Middleware may
    # also be passed as an array; each item in the array must resolve to
    # middleware eventually (i.e., callable or service name).
    #
    # Middleware can be attached to specific paths, allowing you to mix and match
    # applications under a common domain.  The handlers in each middleware
    # attached this way will see a URI with the matched path segment removed.
    #
    # i.e., path of "/api/member/profile" only passes "/member/profile" to $apiMiddleware
    # - $app->pipe('/api', $apiMiddleware);
    # - $app->pipe('/docs', $apiDocMiddleware);
    # - $app->pipe('/files', $filesMiddleware);

    # Register the routing middleware in the middleware pipeline.
    # This middleware registers the Mezzio\Router\RouteResult request attribute.
    $app->pipe(RouteMiddleware::class);

    # The following handle routing failures for common conditions:
    # - HEAD request but no routes answer that method
    # - OPTIONS request but no routes answer that method
    # - method not allowed
    # Order here matters; the MethodNotAllowedMiddleware should be placed
    # after the Implicit*Middleware.
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);

    # Seed the UrlHelper with the routing results:
    $app->pipe(UrlHelperMiddleware::class);

    # Add more middleware here that needs to introspect the routing results; this
    # might include:
    #
    # - route-based authentication
    # - route-based validation
    # - etc.

    # Register the dispatch middleware in the middleware pipeline
    $app->pipe(DispatchMiddleware::class);

    # At this point, if no Response is returned by any middleware, the
    # NotFoundHandler kicks in; alternately, you can provide other fallback
    # middleware to execute.
    $app->pipe(NotFoundHandler::class);
};
