<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusCommand\Agent;
use Laminas\Db\Sql\Expression;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Qore\App\Services\Amadeus\Amadeus;
use Qore\InterfaceGateway\InterfaceGateway;
use Mezzio\Template\TemplateRendererInterface;
use Qore\SynapseManager\Plugin\Filter\Filter;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\SynapseManager\Artificer\Service\Filter as ServiceFilter;

/**
 * Class: AmadeusCommandService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusCommandService extends ServiceArtificer
{
    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = '';

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
        $_router->group('/amadeus', null, function($_router) {
            $_router->any('/execute', 'execute');
            $_router->any('/command', 'command');
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
        $routeResult = $this->model->getRouteResult();

        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);

        list($method, $arguments) = $this->routingHelper->dispatch(['execute','command' => 'index']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ResultInterface
     */
    protected function index($_reload = false) : ?ResultInterface
    {
        $this->next->process($this->model);
        $request = $this->model->getRequest();
        $data = $request->getParsedBody();
        if(isset($data['response'])){
            $mm = Qore::service('mm');
            $newCommand = $mm('SM:AmadeusCommand',[
                'data' => $data['response']['model'],
                'command' => $data['response']['model']['output']['crypticResponse']['command']
            ]);
            $mm($newCommand)->save();
        }
        $component = $this->getComponent(true);

        $ig = Qore::service(InterfaceGateway::class);
        if ($request->isXmlHttpRequest()) {
            return $this->response(
                [ $_reload ? $component : $ig('layout')->component($component) ]
            );
        } else {
            return $this->response(new HtmlResponse(Qore::service(TemplateRendererInterface::class)->render('frontapp::erp-platform/cabinet.twig', [
                'title' => 'История команд amadeus',
                'interface-gateway' => $ig('layout')->component($component)->compose(),
            ])));
        }
    }

    /**
     * Not Found
     *
     * @return ResultInterface
     */
    protected function notFound() : ResultInterface
    {
        return $this->response(new HtmlResponse('Not Found', 404));
    }

    /**
     * Execute - исполняем запрос на выполнение команды от имени агента
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function execute() : ResultInterface
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        if (! isset($queryParams['agent'])) {
            return $this->getResponseResult([
                'response' => new JsonResponse(['result' => 'Enter agent alias'])
            ]);
        }

        if (! isset($queryParams['command'])) {
            return $this->getResponseResult([
                'response' => new JsonResponse(['result' => 'Enter command'])
            ]);
        }

        $entity = $this->mm('SM:AmadeusSession')->where(function($_where) use ($queryParams) {
            $_where(['@this.userAlias' => $queryParams['agent']]);
        })->one();
        $result = [];

        $amadeusService = Qore::service(Amadeus::class);
        $amadeusService->init($entity);

        if (! is_null($entity)) {
            $result = ! is_null($entity->id) ? $amadeusService->execute($queryParams['command']) : null;
            if (! is_null($result)) {
                $decode = json_decode($result, true);
                $result = $decode['output']['crypticResponse']['response'] ?? $decode;
            } else {
                $result = 'Error with command';
            }
        } else {
            $result = 'Alias does not match';
        }

        return $this->response(
            new JsonResponse(['result' => $result])
        );
    }

    /**
     * getComponent
     *
     * @param mixed $_data
     */
    protected function getComponent($_data = null)
    {
        # - Формируем уникальный суффикс для имени компонента интерфейса
        if ($_data === true) {

            $filters = $this->model->getFilters(true);
            $queryParams = $this->model->getRequest()->getQueryParams();
            $offset = $this->limit * ((int)($queryParams['page'] ?? 1) - 1);

                $request = $this->model->getRequest();
                $user = $request->getAttribute(User::class);
                
                $filters = Qore::collection($filters->map(function ($_filter) use ($user){
                    if ($_filter['referencePath'] == '@this.user') {
                        $_filter['filters'] = [
                            'id' => new ServiceFilter($user->id),
                        ];
                    }
                    return $_filter;
                }));
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
                'user' => [
                    'label' => 'Агент',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function($_item) {
                        return ! is_null($_item->user()) ? sprintf('%s (%s)', $_item->user()->fullname, $_item->user()->agentSign) : '';
                    }
                ],
                'command' => [
                    'label' => 'Команда',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                    'transform' => function($_item) {
                        return htmlentities(sprintf('%s', $_item['command']));
                    }
                ],
                'created' => [
                    'label' => 'Дата',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function($_item) {
                        return $_item['__created']->format('d.m.Y H:i');
                    }
                ],
                'data' => [
                    'label' => 'Данные',
                    'class-header' => 'col',
                    'class-column' => 'col',
                    'transform' => function($_item) {
                        return implode(
                            "\n", array_slice( explode("\n", $_item['data']['output']['crypticResponse']['response'] ?? '--', 3), 0, 2)
                        ) . ' ...';
                    }
                ],
            ],
            'actions' => $this->getListActions(),
            'sortable' => $this->getSortableOptions(),
            'filter-form' => $filterForm,
            'componentActions' => ['reload'],
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
    protected function getCount(): int
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
            $request = $this->model->getRequest();
            $user = $request->getAttribute(User::class);
            $filters = Qore::collection($filters->map(function ($_filter) use ($user){
                if ($_filter['referencePath'] == '@this.user') {
                    $_filter['filters'] = [
                        'id' => new ServiceFilter($user->id),
                    ];
                }
                return $_filter;
            }));
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
        return [
           
            'decoder' => [
                'label' => 'Декодер',
                'icon' => 'fas fa-microchip',
                'actionUri' => function($_data) {
                    $decoderArtificer = $this->sm('AmadeusDecoder:Executor');
                    return Qore::url(
                        $decoderArtificer->getRouteName('index'),
                        ['id' => $_data['id']]
                    );
                },
            ],
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
     * Return modal component with command preview
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function viewCommand(): ResultInterface
    {
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $command = $this->gateway(['@this.id' => $routeParams['id']])->one();
        $ig = Qore::service(InterfaceGateway::class);

        $jsonViewer = $ig(JsonViewer::class, 'command-json-view');
        $jsonViewer->setOption('data', $command['data']);

        $modal = $ig(Modal::class, 'modal-view-command');
        $modal->component($jsonViewer);
        $modal->execute('open');

        return $this->response($ig('layout')->component($modal));
    }
}
