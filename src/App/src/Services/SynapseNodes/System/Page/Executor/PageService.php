<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Page\Executor;

use Laminas\Diactoros\Response\RedirectResponse;
use Qore\Qore;
use Qore\DealingManager;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service;
use Mezzio\Router\RouteResult;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Class: PageService
 *
 * @see Service\ServiceArtificer
 */
class PageService extends Service\ServiceArtificer
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/pages', null, function($_router) {
            $_router->any('/[{page:[a-z\-]+}]', 'index');
            # - Register routes from PageComponent services
            $this->initializeRoutes($_router);
        });
    }

    /**
     * compile
     *
     */
    public function compile() : ?DealingManager\ResultInterface
    {
        $routeResult = $this->model->getRouteResult();

        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->getRouteName('index'):
                return $this->index();
            default:
                return $this->index();
        }

        return null;
    }

    /**
     * index
     *
     */
    protected function index()
    {
        # - Get request
        $request = $this->model->getRequest();
        # - Get route result
        $routeResult = $request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();

        $filters = [ '@this.url' => isset($routeParams['page']) && $routeParams['page'] !== '' ? $routeParams['page'] : '/' ];
        $page = $this->getLocalGateway($filters)->one();
        # - Проверка авторизации если таковая требуется
        if ((int)$page->protected && is_null($request->getAttribute('auth-cmf'))) {
            return $this->getResponseResult([
                'response' => new RedirectResponse(Qore::url(
                    $this->getRouteName('index'),
                    [ 'page' => 'signin' ]
                ))
            ]);
        }

        $this->model['page'] = $page;

        # - Process all nodes of this synapse
        $response = $this->next->process($this->model);

        if (isset($this->model['response'])) {
            return $this->model['response'];
        } elseif ($request->isXmlHttpRequest()) {
            if (isset($this->model['components'])) {
                return $this->getResponseResult(['response' => new JsonResponse($this->model['components'])]);
            }
            return $this->getResponseResult(['response' => new JsonResponse($this->model['page']['pageComponentData'])]);
        } else {
            return $this->getResponseResult([
                'response' => new HtmlResponse(
                    Qore::service(TemplateRendererInterface::class)->render(sprintf('frontapp::%s', $page->template), [
                        'title' => $page->title,
                        'model' => $this->model,
                        'page' => $this->model['page'],
                    ])
                )
            ]);
        }
    }

    /**
     * Initialize Routes
     *
     * @return
     */
    protected function initializeRoutes(RouteCollector $_router)
    {
        $pageContent = $this->mm('QSynapse:Synapses')->with('services')->where(function ($_where) {
            $_where(['@this.name' => 'PageComponent']);
        })->one();

        if (is_null($pageContent->services()) || ! $pageContent->services()->count()) {
            return;
        }

        foreach ($pageContent->services() as $service) {
            $this->sm('PageComponent:' . $service->name)->routes($_router);
        }
    }

}
