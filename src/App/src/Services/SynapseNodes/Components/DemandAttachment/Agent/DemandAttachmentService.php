<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\DemandAttachment\Agent;

use Laminas\Diactoros\Response\EmptyResponse;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: DemandAttachmentsService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class DemandAttachmentService extends ServiceArtificer
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
        $_router->group('/attachments', null, function($_router) {
            $_router->get('/{id:\d+}', 'download');
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
        $routeResult = $this->model->getRouteResult();

        $this->routingHelper = $this->plugin(RoutingHelper::class);

        list($method, $arguments) = $this->routingHelper->dispatch(['download']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Download attachment
     *
     * @return ?ResultInterface 
     */
    public function download(): ?ResultInterface
    {
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $attachment = $this->mm()->where(['@this.id' => $routeParams['id']])->one();

        if (! is_null($attachment)) {
            return $this->response(new EmptyResponse(200, [
                'X-Accel-Redirect' => $attachment->file()->getUri(),
                'Content-Type' => $attachment['type'],
                'Content-Disposition' => sprintf('attachment; filename="%s"', $attachment['filename']),
            ]));
        } else {
            return $this->response(new EmptyResponse(404));
        }

    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Hi from Qore\App\SynapseNodes\Components\DemandAttachments\Agent - DemandAttachmentsService'));
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
