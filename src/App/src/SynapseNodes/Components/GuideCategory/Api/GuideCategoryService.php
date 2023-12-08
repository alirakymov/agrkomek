<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\GuideCategory\Api;

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
 * Class: ArticleService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class GuideCategoryService extends ServiceArtificer
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
        $_router->group('/guide-category', null, function($_router) {
            $_router->get('/list', 'list');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['list']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Hi from Qore\App\SynapseNodes\Components\Article\Api - ArticleService'));
    }

    /**
     * List
     *
     * @return ?ResultInterface
     */
    protected function list(): ?ResultInterface
    {
        $data = $this->mm()->all();
        $data2 = Qore::collection($data->toList());

        $data = $data->map(function($_item) use ($data2) {
            $_item = $_item->toArray(true);
            $_item['isLeaf'] = true;
            if ($data2->match(['__idparent' => $_item['id']])->count()) {
                $_item['isLeaf'] = false;
            } 
            return $_item;
        });

        return $this->response(new JsonResponse($data->toList()));
    }

    /**
     * Not Found
     *
     * @return ?ResultInterface
     */
    protected function notFound() : ?ResultInterface
    {
        return $this->response(new JsonResponse([
            'error' => 'resource not found'
        ], 404));
    }

}
