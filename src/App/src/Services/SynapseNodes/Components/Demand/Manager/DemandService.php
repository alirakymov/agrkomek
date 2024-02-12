<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Demand\Manager;

use Laminas\Db\Sql\Expression;
use Mezzio\Helper\UrlHelper;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Decorator\QoreFront;
use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\Filter\Filter;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\Indexer\Indexer;
use Qore\SynapseManager\Plugin\Report\Report;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: DemandService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class DemandService extends ServiceArtificer
{
    /**
     * sortable
     *
     * @var mixed
     */
    private $sortable = false;

    /**
     * @var int
     */
    private $limit = 25;

    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = 'DemandForm';

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
        $_router->group('/demand', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        list($method, $arguments) = $this->routingHelper->dispatch() ?? [null, null];

        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
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
     * index
     *
     */
    protected function index($_reload = false)
    {
        $this->next->process($this->model);
        $request = $this->model->getRequest();

        $component = $this->getComponent(true);
        $ig = Qore::service(InterfaceGateway::class);

        if ($request->isXmlHttpRequest()) {
            return $this->response(
                [ $_reload ? $component : $ig('layout')->component($component) ]
            );
        } else {
            return $this->response(new HtmlResponse(Qore::service(TemplateRendererInterface::class)->render('app::main', [
                'title' => 'Управляющий сервис - Управляющий сервис',
                'frontProtocol' => $ig('layout')->component($component)->compose(),
            ])));
        }
    }

    /**
     * Report
     *
     * @return ?ResultInterface 
     */
    protected function report(): ?ResultInterface
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        $filters = $this->model->getFilters(true);

        /** @var Report */
        $report = $this->sm('Demand:Report')->plugin(Report::class);
        $report->build($filters);

        return $this->response($this->getComponent(null));
    }

    /**
     * Report
     *
     * @return ?ResultInterface 
     */
    protected function reportDownload(): ?ResultInterface
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        /** @var Report */
        $report = $this->plugin(Report::class);

        return $this->response($report->download($routeParams['id']));
    }

    /**
     * Report
     *
     * @return ?ResultInterface 
     */
    protected function reportRemove(): ?ResultInterface
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        /** @var Report */
        $report = $this->plugin(Report::class);
        $report->remove($routeParams['id']);

        return $this->response($this->getComponent(null));
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

        return $this->response();
    }

    /**
     * create
     *
     */
    protected function create()
    {
        # - Init synpase structure
        // $this->next->process($this->model);

        $request = $this->model->getRequest();

        /** @var FormMaker */
        $formMaker = $this->plugin(FormMaker::class);
        $fm = $formMaker->make($this->serviceForm);

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-create'))
            ->setTitle('Создание')
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                # - Save data
                $this->mm($this->model->getDataSource()->extractData()->first())->save();

                # - Generate json response
                return $this->response([
                    $modal->execute('close'),
                    $component->execute('reload'),
                ]);
            } else {
                return $this->response($fm->decorate(['decorate']));
            }
        } else {
            $modal->execute('open');
            # - Generate json response
            return $this->response($ig('layout')->component($modal));
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

        /** @var FormMaker */
        $formMaker = $this->plugin(FormMaker::class);
        $fm = $formMaker->make($this->serviceForm);

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-update'))
            ->setTitle('Редактирование')
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                # - Save data
                $this->model->getDataSource()->extractData()->each(function($_entity){
                    $this->mm($_entity)->save();
                });
                # - Generate json response
                return $this->response([
                    $modal->execute('close'),
                    $component->execute('reload'),
                ]);
            } else {
                return $this->response(
                    $fm->decorate(['decorate'])
                );
            }
        } else {
            $modal->execute('open');
            # - Generate json response
            return $this->response($ig('layout')->component($modal));
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

        $object = $this->gateway([
            '@this.id' => $routeParams['id']
        ])->one();

        ! is_null($object) && $this->mm($object)->delete();

        $component = $this->getComponent();
        return $this->response([$component->execute('reload')]);
    }

    /**
     * Default action process
     *
     * @return ResultInterface
     */
    protected function default()
    {
        $this->next->process($this->model);

        return new Result([
            'response' => $this->getComponent(true)
        ]);
    }

    /**
     * getComponent
     *
     * @param mixed $_data
     */
    protected function getComponent($_data = null)
    {
        $count = 0;
        if ($_data === true) {
            $filters = $this->model->getFilters(true);
            $queryParams = $this->model->getRequest()->getQueryParams();
            $offset = $this->limit * ((int)($queryParams['page'] ?? 1) - 1);

            /** @var Indexer */ 
            $indexer = $this->sm('Demand:Indexer')->plugin(Indexer::class);

            $results = $indexer->search($filters, [
                'query' => $queryParams['query'] ?? null,
                'limit' => $this->limit,
                'offset' => $offset,
                'sort' => ['id' => 'desc'],
            ]);


            $ids = [];
            foreach ($results as $item) {
                $ids[] = $item->getId();
            }

            $count = $results->getTotal();

            $_data = $ids ? $this->gateway(['@this.id' => $ids])->select(fn ($_select) => $_select->order('@this.id desc'))->all() : null;
        }

        /** @var Filter */
        $filter = $this->plugin(Filter::class);
        $filterForm = $filter->getForm($this->model->getRequest(), $this->model->getFilters(true))->decorate('decorate');

        $routeResult = $this->model->getRouteResult();

        $reports = $this->sm('Demand:Report')->plugin(Report::class)->getReports($this);

        return $this->presentAs(ListComponent::class, [
            'actions' => $this->getListActions(),
            'suffix' => $testFilters['filters']['id'] ?? null,
            'sortable' => $this->getSortableOptions(),
            'reports' => $reports,
            'filter-form' => $filterForm,
            'componentActions' => [
                'report' => [
                    'icon' => 'fas fa-file-csv',
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName($this, 'report'),
                        $routeResult->getMatchedParams(),
                        $this->model->getSubjectFilters()
                    ),
                ],
                'reload',
            ],
            'pagination' => [
                'count' => $count,
                'page' => (int)($queryParams['page'] ?? 1),
                'per-page' => $this->limit,
            ]
        ])->build($_data);
    }

    /**
     * return count of products
     *
     * @return int
     */
    protected function getCount() : int
    {
        $filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
            foreach ($_artificerFilter['filters'] as $attribute => $value) {
                $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
            }
            return $_result;
        }, []);

        if ($this->isTreeStructure() && ! isset($filters['@this.__idparent'])) {
            $filters['@this.__idparent'] = 0;
        }

        $gw = $this->getLocalGateway($filters);
        $select = (clone $gw)->select(fn($_select) => $_select->columns(['@this.id' => 'id'], true, false))->buildSelect();

        return (int)$this->mm()->select(function($_select) {
            $_select->columns(['@this.count' => new Expression('count(*)')])
                ->limit(1);
        })->where(fn($_where) => $_where->in('@this.id', $select))->all()->extract('count')->first();
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
            return [
                'demand-dialog' => [
                    'label' => 'Диалог',
                    'icon' => 'fas fa-envelope-open-text',
                    'actionUri' => function($_data) {
                        $artificer = $this->sm('DemandMessage:Manager');
                        return Qore::service(UrlHelper::class)->generate(
                            $this->getRouteName(get_class($artificer), 'index'),
                            [],
                            $artificer->getFilters('Demand:Connector', ['id' => $_data['id']])
                        );
                    },
                ],
                'update', 'delete',
            ];

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
        if (isset($this->requestFilters['__idparent'])) {
            $optionsStorage = $this->getLocalGateway(['id' => $this->requestFilters['__idparent']])->one();
        } else  {
            /**
                $filter = $this->model->getFilters(true)->firstMatch([
                    'namespace' => sprintf('%s.%s', $this->getNameIdentifier(), '{RelatedSynapse:Service}'),
                ]);

                if (isset($filter['filters']['id'])) {
                    $optionsStorage = $this->mm('{RelatedSynapse}')->where(function($_where) use ($filter) {
                        $_where(['id' => $filter['filters']['id']]);
                    })->one();
                }
            */
        }

        return $optionsStorage;
    }

}
