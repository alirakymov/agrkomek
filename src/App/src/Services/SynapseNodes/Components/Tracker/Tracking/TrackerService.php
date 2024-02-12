<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Tracker\Tracking;

use Laminas\Diactoros\Response\HtmlResponse;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: TrackerService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class TrackerService extends ServiceArtificer
{

    /**
     * @var \Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper
     */
    private RoutingHelper $routingHelper;

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        $_router->group('/tracker', null, function($_router) {
            # - Register routes
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
        # - Register this service forms routes
        $this->registerFormsRoutes($_router);
    }

    /**
     * Execute current service
     *
     * @return ?ResultInterface
     */
    public function compile() : ?ResultInterface
    {
        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        list($method, $arguments) = $this->routingHelper->dispatch() ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Hi from Qore\App\SynapseNodes\Components\Tracker\Tracking - TrackerService'));
    }

    /**
     * Not Found
     *
     * @return ?ResultInterface
     */
    protected function notFound() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Not Found', 404));
    }

}
