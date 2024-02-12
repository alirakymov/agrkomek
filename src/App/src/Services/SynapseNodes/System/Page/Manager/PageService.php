<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Page\Manager;


use Qore\Front as QoreFront;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\DealingManager;
use Qore\Debug\DebugBar;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: Service
 *
 * @see SynapseNodes\BaseManagerServiceArtificer
 */
class PageService extends ServiceArtificer
{
    /**
     * @var RoutingHelper
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
        $_router->group('/page', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            # - Register related subjects routes
            $this->registerSubjectsRoutes($_router);
        });
    }

    /**
     * compile
     *
     */
    public function compile() : ?DealingManager\ResultInterface
    {
        $routeResult = $this->model->getRouteResult();
        $this->routingHelper = $this->plugin(RoutingHelper::class);

        $routeResult = $this->model->getRouteResult();
        $this->routingHelper = $this->plugin(RoutingHelper::class);

        list($method, $arguments) = $this->routingHelper->dispatch() ?? [null, null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * index
     *
     */
    protected function index($_reload = false)
    {
        $this->next->process($this->model);

        $list = $this->presentAs(ListComponent::class, [
            'title' => 'Страницы',
            'columns' => [
                'id' => [
                    'label' => '#',
                    'model-path' => 'id',
                    'class-header' => 'text-center col-sm-1',
                    'class-column' => 'text-center col-sm-1',
                ],
                'name' => [
                    'label' => 'Заголовок',
                    'transform' => function($_item) {
                        return [
                            'label' => $_item['title'],
                            'actionUri' => Qore::service(UrlHelper::class)->generate(
                                $this->getRouteName('index'),
                                $this->model->getRouteResult()->getMatchedParams(),
                                array_merge($this->model->getSubjectFilters(), $this->getFilters($this, [
                                    '__idparent' => $_item['id']
                                ]))
                            ),
                        ];
                    }
                ],
                'description' => [
                    'label' => 'Описание',
                    'transform' => function ($_item) {
                        return $_item->description;
                    }
                ],
                'url' => [
                    'label' => 'Адрес',
                    'transform' => function ($_item) {
                        return $_item->url;
                    },
                ],
            ],
            'actions' => $this->getListActions(),
        ])->build($this->getData())->inBlock(true);

        if ($_reload) {
            return $this->getResponseResult([
                'response' => QoreFront\ResponseGenerator::get($list)
            ]);
        } else {
            return $this->getResponseResult([
                'response' => new HtmlResponse(Qore::service(TemplateRendererInterface::class)->render('app::main', [
                    'title' => 'Qore.App - Управление страницами',
                    'frontProtocol' => $this->getFrontProtocol($this->model->getRequest())
                        ->component($list)->asArray(),
                ]))
            ]);
        }
    }

    /**
     * create
     *
     */
    protected function create()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();

        $modal = QoreFront\Protocol\Component\QCModal::get('create-service-subject')
            ->setTitle('Создание нового элемента')
            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm = $this->getServiceForm('PageForm')));

        if ($request->getMethod() === 'POST') {
            # - Save data
            $this->mm($this->model->getDataSource()->extractData()->first())->save();
            # - Generate json response
            return $this->getResponseResult([
                'response' => QoreFront\ResponseGenerator::get(
                    $modal->run('close'),
                    $this->presentAs(ListComponent::class)->build()->run('reload')
                )
            ]);
        } else {
            # - Get front protocol instance
            $front = $this->getFrontProtocol($request);
            # - Create modal and set form
            $front->component($modal->run('open'));
            # - Generate json response
            return $this->getResponseResult([
                'response' => QoreFront\ResponseGenerator::get($front)
            ]);
        }
    }

    /**
     * update
     *
     */
    protected function update()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();

        $modal = QoreFront\Protocol\Component\QCModal::get('create-service-subject')
            ->setTitle('Создание нового элемента')
            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm = $this->getServiceForm('PageForm')));

        if ($request->getMethod() === 'POST') {
            # - Save data
            $this->mm($this->model->getDataSource()->extractData()->first())->save();
            # - Generate json response
            return $this->getResponseResult([
                'response' => QoreFront\ResponseGenerator::get(
                    $modal->run('close'),
                    $this->presentAs(ListComponent::class)->build()->run('reload')
                )
            ]);
        } else {
            # - Get front protocol instance
            $front = $this->getFrontProtocol($request);
            # - Create modal and set form
            $front->component($modal->run('open'));
            # - Generate json response
            return $this->getResponseResult([
                'response' => QoreFront\ResponseGenerator::get($front)
            ]);
        }
    }

    /**
     * runDelete
     *
     */
    protected function delete()
    {
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $block = $this->gateway([
            '@this.id' => $routeParams['id']
        ])->one();

        $this->mm($block)->delete();

        return $this->getResponseResult([
            'response' => QoreFront\ResponseGenerator::get(
                $this->presentAs(ListComponent::class)->build()->run('reload')
            )
        ]);
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        $actions = [
            'widgets' => [
                'label' => 'Виджеты страницы',
                'icon' => 'fa fa-bars',
                'actionUri' => function($_data) {
                    $pagePlaces = $this->sm->getServicesRepository()->findByName('PageWidget:Manager');
                    return Qore::service(UrlHelper::class)->generate($this->getRouteName(get_class($pagePlaces), 'index'), [], $pagePlaces->getFilters($this, ['id' => $_data['id']]));
                },
            ],
        ];

        return array_merge($actions, $this->getDefaultListActions());
    }

    /**
     * getData
     *
     */
    protected function getData()
    {
        $filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
            foreach ($_artificerFilter['filters'] as $attribute => $value) {
                $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
            }
            return $_result;
        }, []);

        if (! isset($filters['@this.__idparent'])) {
            $filters['@this.__idparent'] = 0;
        }

        $data = $this->gateway($filters)->all();
        return $data;
    }

}
