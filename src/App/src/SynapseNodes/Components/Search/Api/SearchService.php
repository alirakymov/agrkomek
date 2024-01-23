<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Search\Api;

use Manticoresearch\ResultHit;
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
use Qore\SynapseManager\Plugin\Indexer\Indexer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: ArticleService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class SearchService extends ServiceArtificer
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
        $_router->group('/search', null, function($_router) {
            $_router->get('', 'search');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['search']) ?? ['notFound', null];
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
    protected function search(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        $sm = Qore::service(SynapseManager::class);
        $service = $sm('Article:Api');
        $indexer = $service->plugin(Indexer::class);

        /**@var ResultHit[]*/
        $results = $indexer->search(Qore::collection([]), [
            'query' => $queryParams['q'] ?? null,
            'limit' => 10,
            'offset' => 0,
            'sort' => ['id' => 'desc'],
        ]);

        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result->getId();
        }

        $articles = $this->mm('SM:Article')->where(['@this.id' => $ids])->all()->map(fn ($_article) => $_article->toArray(true));

        $service = $sm('Guide:Api');
        $indexer = $service->plugin(Indexer::class);

        /**@var ResultHit[]*/
        $results = $indexer->search(Qore::collection([]), [
            'query' => $queryParams['q'] ?? null,
            'limit' => 10,
            'offset' => 0,
            'sort' => ['id' => 'desc'],
        ]);

        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result->getId();
        }

        $guides = $this->mm('SM:Guide')->where(['@this.id' => $ids])->all()->map(fn ($_guide) => $_guide->toArray(true));

        return $this->response(new JsonResponse([
            'articles' => $articles->toList(),
            'guides' => $guides->toList(),
        ]));
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
