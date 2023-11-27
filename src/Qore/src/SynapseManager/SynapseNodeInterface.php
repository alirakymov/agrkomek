<?php

namespace Qore\SynapseManager;

use Qore\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface SynapseNodeInterface
{
    /**
     * registerRoutes
     *
     * @param RouteCollector $_router
     */
    public function registerRoutes(RouteCollector $_router) : SynapseNodeInterface;

    /**
     * processChain
     *
     * @param ServerRequestInterface $request
     */
    public function processChain(ServerRequestInterface $request) : ResponseInterface;
}
