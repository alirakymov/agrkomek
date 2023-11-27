<?php

namespace Qore\Middleware\Action;

use Qore\Router\RouteCollector;

interface ActionMiddlewareInterface
{
    /**
     * Return action routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void;
}
