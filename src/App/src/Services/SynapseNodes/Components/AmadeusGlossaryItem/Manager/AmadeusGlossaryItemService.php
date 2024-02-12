<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusGlossaryItem\Manager;

use Laminas\Db\Sql\Expression;
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
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\Filter\Filter;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\Indexer\Indexer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: AmadeusGlossaryItemService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusGlossaryItemService extends ServiceArtificer
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
    private $serviceForm = 'AmadeusGlossaryItemForm';

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
        $_router->group('/amadeus-glossary-item', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            $_router->any('/search', 'search');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['search']) ?? [null, null];

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
     * Index action
     *
     * @param $_reload (optional)
     *
     * @return ?ResultInterface
     */
    protected function index($_reload = false): ?ResultInterface
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
                'title' => 'Управляющий сервис - Управляющий сервис элементов справочника',
                'frontProtocol' => $ig('layout')->component($component)->compose(),
            ])));
        }
    }

    /**
     * Search objects
     *
     * @return ?ResultInterface
     */
    protected function search(): ?ResultInterface {
        $indexer = $this->sm('AmadeusGlossaryItem:Indexer')->plugin(Indexer::class);

        $params = [
            'index' => $indexer->getIndexName(),
            'body'  => [
                'query' => [
                    'query_string' => [
                        'query' => '(ALA OR NQZ OR Almaty) AND (idGlossary:0)',
                    ],
                ],
            ]
        ];

        $elastic = $indexer->getElastic();
        $results = $elastic->search($params);

        $component = $this->getComponent(true);
        $ig = Qore::service(InterfaceGateway::class);

        return $this->response($component);
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
        $this->next->process($this->model);

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
        $routeResult = $this->model->getRouteResult();

        if ($_data === true) {
            $filters = $this->model->getFilters(true);
            $queryParams = $this->model->getRequest()->getQueryParams();
            $offset = $this->limit * ((int)($queryParams['page'] ?? 1) - 1);

            if ($this->entity->isIndexed()) {

                /** @var Indexer */
                $indexer = $this->plugin(Indexer::class);

                $results = $indexer->search($filters, [
                    'query' => $queryParams['query'] ?? null,
                    'limit' => $this->limit,
                    'offset' => $offset,
                ]);

                $ids = [];
                foreach ($results as $item) {
                    $ids[] = $item->getId();
                }


                $gw = $this->gateway(['@this.id' => $ids]);

            } else {

                $filters = $filters->reduce(function($_result, $_artificerFilter){
                    foreach ($_artificerFilter['filters'] as $attribute => $value) {
                        $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
                    }
                    return $_result;
                }, []);

                $gw = $this->gateway($filters)
                    ->select(function($_select) use ($offset) {
                        $queryParams = $this->model->getRequest()->getQueryParams();
                        $_select->offset($offset)->limit($this->limit);
                        $_select->order('@this.id desc');
                    });
            }

            $_data = $gw->all();
        }

        /** @var Filter */
        $filter = $this->plugin(Filter::class);
        $filterForm = $filter->getForm($this->model->getRequest(), $this->model->getFilters(true))->decorate('decorate');

        return $this->presentAs(ListComponent::class, [
            'columns' => [
                'id' => [
                    'label' => '#',
                    'class-header' => 'col-1 text-center',
                    'class-column' => 'col-1 text-center',
                ],
                'code' => [
                    'label' => 'Код',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                ],
                'data' => [
                    'label' => 'Справочные данные',
                    'class-header' => 'col',
                    'class-column' => 'col',
                    'transform' => function($_item) {
                        return ! is_null($_item['data']) && ! is_string($_item['data'])
                            ? json_encode($_item['data'], JSON_UNESCAPED_UNICODE)
                            : ($_item['data'] ?? '');
                    }
                ],
            ],
            'actions' => $this->getListActions(),
            'sortable' => $this->getSortableOptions(),
            'filter-form' => $filterForm,
            'filter' => [
                'fulltext' => true,
                'url' => Qore::url(
                    $this->getRouteName('search'),
                    $routeResult->getMatchedParams(),
                    $this->model->getSubjectFilters()
                )
            ],
            'pagination' => [
                'count' => $this->getCount(),
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
        $filters = $this->model->getFilters(true);

        if ($this->entity->isIndexed()) {
            /** @var Indexer */
            $queryParams = $this->model->getRequest()->getQueryParams();
            $indexer = $this->plugin(Indexer::class);
            $results = $indexer->search($filters, [
                'query' => $queryParams['query'] ?? null,
            ]);
            return $results->getTotal();
            
        } else {
            $filters = $filters->reduce(function($_result, $_artificerFilter){
                foreach ($_artificerFilter['filters'] as $attribute => $value) {
                    $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
                }
                return $_result;
            }, []);

            $gw = $this->gateway($filters);
            return (int)$gw->select(function($_select) {
                $_select->columns(['@this.count' => new Expression('count(*)')])
                    ->limit(1);
            })->all()->extract('count')->first();
        }
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
                    'icon' => 'fas fa-bars',
                    'actionUri' => function($_data) {
                        $artificer = $this->sm('{Synapse:Service}');
                        return Qore::service(UrlHelper::class)->generate(
                            $this->getRouteName(get_class($artificer), 'index'),
                            [],
                            $artificer->getFilters($this, ['id' => $_data['id']])
                        );
                    },
                ],
                'update', 'delete',
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
