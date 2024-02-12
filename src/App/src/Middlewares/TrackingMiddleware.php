<?php

declare(strict_types=1);

namespace Qore\App\Middlewares;

use Qore\Qore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\Services\Tracking\TrackingInterface;

/**
 * Class: RoutesMiddleware
 *
 * @see BaseMiddleware
 */
class TrackingMiddleware implements MiddlewareInterface
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
        /** @var Tracking */
        $tracking = Qore::service(TrackingInterface::class);

        return $tracking(function() use ($_handler, $_request) {
            return $_handler->handle($_request);
        });
    }

}

