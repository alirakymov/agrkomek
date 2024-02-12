<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication;

use Laminas\Diactoros\Response\HtmlResponse;
use Qore\App\SynapseNodes\BaseExecutorServiceArtificer;
use Qore\DealingManager\ResultInterface;
use Qore\Router\RouteCollector;

/**
 * Class: SynapseService
 *
 * @see SynapseNodes\BaseExecutorServiceArtificer
 */
class AuthenticationService extends BaseExecutorServiceArtificer
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/merchant-authentication', null, function($_router) {
            $_router->post('/', 'index');
        });
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        $routeResult = $this->model->getRouteResult();
        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->getRouteName('index'):
                return $this->index();
        }

        return null;
    }

    /**
     * index
     *
     */
    public function index() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Hello world'));
    }

}
