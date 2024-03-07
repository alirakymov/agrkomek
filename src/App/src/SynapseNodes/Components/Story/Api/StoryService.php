<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Story\Api;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Qore\App\SynapseNodes\Components\Machinery\Machinery;
use Qore\App\SynapseNodes\Components\Story\Story;
use Qore\DealingManager\ResultInterface;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: ArticleService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class StoryService extends ServiceArtificer
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
        $_router->group('/story', null, function($_router) {
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
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        $filters = ['@this.active' => 1];

        $gw = $this->mm();

        if ($filters) {
            $gw->where($filters);
        }

        $data = $gw->all();

        $sortOrder = $this->getSortableOptions();
        $data = $data->sortBy(function($_item) use ($sortOrder) {
            return (int)array_search($_item->id, array_values($sortOrder));
        }, SORT_ASC);

        $data = $data->map(fn ($_item) => $_item->toArray(true));
        return $this->response(new JsonResponse($data->toList()));
    }

    /**
     * Get order option name in options storage array
     *
     * @return string
     */
    public function getOrderOptionName() : string
    {
        return sprintf('%s-order', $this->entity->synapse()->name);
    }

    /**
     * getSortableOptions
     *
     */
    protected function getSortableOptions()
    {
        $storage = $this->getOptionsStorage();
        return $storage['__options'][$this->getOrderOptionName()] ?? [];
    }

    /**
     * getOptionsStorage
     *
     */
    protected function getOptionsStorage()
    {
        $optionsStorage = $this->mm('SM:Settings')->where(['@this.key' => Story::class])->one();

        if (is_null($optionsStorage)) {
            $optionsStorage = $this->mm('SM:Settings', [
                'key' => Story::class,
            ]);
        }

        return $optionsStorage;
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
