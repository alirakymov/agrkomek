<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Office\Executor;

use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\DealingManager\ResultInterface;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: OfficeService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class OfficeService extends ServiceArtificer
{
    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = '';

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
        $_router->group('/', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
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
        $request = $this->model->getRequest();
        $ig = Qore::service(InterfaceGateway::class);
        if ($request->isXmlHttpRequest()) {
            return $this->response([]);
        } else {
            return $this->response(new HtmlResponse(
                Qore::service(TemplateRendererInterface::class)->render('frontapp::erp-platform/cabinet.twig', [
                    'title' => 'Кабинет агента',
                    'interface-gateway' => $ig(Layout::class, 'layout')
                        ->compose(),
                ])
            ));
        }
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
