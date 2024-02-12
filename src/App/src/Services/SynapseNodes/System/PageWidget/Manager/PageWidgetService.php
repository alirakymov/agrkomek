<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\PageWidget\Manager;

use Qore\Qore as Qore;
use Qore\Front as QoreFront;
use Qore\App\SynapseNodes;
use Qore\Router\RouteCollector;
use Qore\DealingManager;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Qore\SynapseManager\Artificer\Service;
use Qore\Debug\DebugBar;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: PageWidgetService
 *
 * @see SynapseNodes\BaseManagerServiceArtificer
 */
class PageWidgetService extends ServiceArtificer
{
    /**
     * sortable
     *
     * @var mixed
     */
    private $sortable = true;

    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = 'PageWidgetForm';

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
        $_router->group('/page-widget', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            # - Register related subjects routes
            $this->registerSubjectsRoutes($_router);
            # - Register this subject forms routes
            $this->registerFormsRoutes($_router);
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
            case $routeResult->getMatchedRouteName() === $this->getRouteName('reload'):
                return $this->index(true);
            case $routeResult->getMatchedRouteName() === $this->getRouteName('create'):
                return $this->create();
            case $routeResult->getMatchedRouteName() === $this->getRouteName('update'):
                return $this->update();
            case $routeResult->getMatchedRouteName() === $this->getRouteName('delete'):
                return $this->delete();
            default:
                return $this->dispatchRoute();
        }

        return null;
    }

    /**
     * index
     *
     */
    protected function index($_reload = false)
    {
        $this->next->process($this->model);
        $request = $this->model->getRequest();

        $component = $this->getComponent(true);

        if ($_reload) {
            return $this->getResponseResult([
                'response' => QoreFront\ResponseGenerator::get($component)
            ]);
        } else {
            if ($request->isXmlHttpRequest()) {
                return $this->getResponseResult([
                    'response' => QoreFront\ResponseGenerator::get(
                        $this->getFrontProtocol($this->model->getRequest())->component($component)
                    )
                ]);
            } else {
                return $this->getResponseResult([
                    'response' => new HtmlResponse(Qore::service(TemplateRendererInterface::class)->render('app::main', [
                        'title' => 'Управление виджетами - Управление виджетами',
                        'frontProtocol' => $this->getFrontProtocol($this->model->getRequest())
                            ->component($component)->asArray(),
                    ]))
                ]);
            }
        }
    }

    /**
     * reorder
     *
     */
    protected function reorder()
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $component = $this->getComponent();

        if ($request->getMethod() === 'POST'
            && $this->sortable && ! is_null($storage = $this->getOptionsStorage())) {
            # - Save data
            $requestData = $request->parseJsonBody();
            if (isset($requestData['data'])) {
                $storage['__options'] = array_merge($storage['__options'] ?? [], [
                    $this->getOrderOptionName() => $requestData['data']
                ]);
                $this->mm($storage)->save();
            }
        }

        return $this->getResponseResult([
            'response' => QoreFront\ResponseGenerator::get()
        ]);
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
            ->setTitle('Создание')
            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm = $this->getServiceForm($this->serviceForm)));

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                $this->mm($this->model->getDataSource()->extractData()->first())->save();
                # - Generate json response
                return $this->getResponseResult([
                    'response' => QoreFront\ResponseGenerator::get(
                        $modal->run('close'),
                        $component->run('reload')
                    )
                ]);
            } else {
                # - Generate json response
                return $this->getResponseResult([
                    'response' => QoreFront\ResponseGenerator::get(
                        $fm->decorate(['decorate'])
                    )
                ]);
            }
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

        $debugBar = Qore::service(DebugBar::class);
        $debugBar['time']->startMeasure('form', 'Form generation');
        $request = $this->model->getRequest();

        $modal = QoreFront\Protocol\Component\QCModal::get('update-service-subject')
            ->setTitle('Редактирование')
            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm = $this->getServiceForm($this->serviceForm)));

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                # - Save data
                $this->model->getDataSource()->extractData()->each(function($_entity){
                    $this->mm($_entity)->save();
                });
                # - Generate json response
                return $this->getResponseResult([
                    'response' => QoreFront\ResponseGenerator::get(
                        $modal->run('close'),
                        $component->run('reload')
                    )
                ]);
            } else {
                # - Generate json response
                return $this->getResponseResult([
                    'response' => QoreFront\ResponseGenerator::get(
                        $fm->decorate(['decorate'])
                    )
                ]);
            }
        } else {
            # - Get front protocol instance
            $front = $this->getFrontProtocol($request);
            # - Create modal and set form
            $front->component($modal->run('open'));
            # - Generate json response
            $debugBar['time']->stopMeasure('form');
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

        $component = $this->getComponent();

        return $this->getResponseResult([
            'response' => QoreFront\ResponseGenerator::get(
                $component->run('reload')
            )
        ]);
    }

    /**
     * getComponent
     *
     * @param mixed $_data
     */
    protected function getComponent($_data = null)
    {
        return $this->presentAs(ListComponent::class, [
            'columns' => [
                'id' => [
                    'label' => '#',
                    'class-header' => 'col-sm-1',
                    'class-column' => 'col-sm-1',
                ],
                'name' => [
                    'label' => 'Название',
                    'class-header' => 'col-sm-2',
                    'class-column' => 'col-sm-2',
                    'transform' => function($_item) {
                        return [
                            'label' => $_item['name'],
                            'actionUri' => Qore::url(
                                $this->getRouteName('index'),
                                $this->model->getRouteResult()->getMatchedParams(),
                                array_merge($this->model->getSubjectFilters(), $this->getFilters($this, [
                                    '__idparent' => $_item['id']
                                ]))
                            ),
                        ];
                    },
                ],
                'service' => [
                    'label' => 'Тип виджета',
                    'class-header' => 'col-sm-9',
                    'class-column' => 'col-sm-9',
                    'transform' => function($_item) {
                        return $_item->service
                            ? (
                                $_item->isSystemService()
                                ? $_item->getSystemServiceLabel()
                                : (! is_null($service = $this->sm($_item->service)) ? $service->getEntity()->label : 'Сервис не найден')
                            )
                            : 'Не определен';
                    },
                ],
            ],
            'actions' => $this->getListActions(),
            'sortable' => $this->getSortableOptions(),
        ])->build($_data);
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        /**
            return [
                '{structure}' => [
                    'label' => '{Structure Button Label}',
                    'icon' => 'fa fa-navicon',
                    'actionUri' => function($_data) {
                        $artificer = $this->sm->getServicesRepository()->findByName('{Synapse:Service}');
                        return Qore::service(UrlHelper::class)->generate($this->getRouteName(get_class($artificer), 'index'), [], $artificer->getFilters($this, ['id' => $_data['id']]));
                    },
                ]
            ];
         */

        return [];
    }

    /**
     * getSortableOptions
     *
     */
    protected function getSortableOptions()
    {
        if (! $this->sortable) {
            return false;
        }

        $storage = $this->getOptionsStorage();
        return $storage['__options'][$this->getOrderOptionName()] ?? [];
    }

    /**
     * getOptionsStorage
     *
     */
    protected function getOptionsStorage()
    {
        $optionsStorage = null;
        if (isset($this->requestFilters['__idparent']) && (int)$this->requestFilters['__idparent'] !== 0) {
            $optionsStorage = $this->getLocalGateway(['@this.id' => $this->requestFilters['__idparent']])->one();
        } else  {
            $filter = $this->model->getFilters(true)->firstMatch([
                'namespace' => sprintf('%s.%s', $this->getNameIdentifier(), 'Page:Manager'),
            ]);

            if (isset($filter['filters']['id'])) {
                $optionsStorage = $this->mm('Page')->where(function($_where) use ($filter) {
                    $_where(['@this.id' => $filter['filters']['id']]);
                })->one();
            }
        }

        return $optionsStorage;
    }

    /**
     * getOrderOptionName
     *
     */
    public function getOrderOptionName()
    {
        return sprintf('%s-order', $this->getNameIdentifier());
    }

}
