<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusCommand\Manager;

use Laminas\Db\Sql\Expression;
use Mezzio\Helper\UrlHelper;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;
use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Qore\InterfaceGateway\Component\JsonViewer;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\Filter\Filter;
use Qore\SynapseManager\Plugin\Indexer\Indexer;
use Qore\SynapseManager\Plugin\Report\Report;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: AmadeusCommandService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusCommandService extends ServiceArtificer
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
        $_router->group('/amadeus-command', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            $_router->get('/view/{id:\d+}', 'view');
        });
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        list($method, $arguments) = $this->routingHelper->dispatch([
            'view' => 'viewCommand',
            'report',
        ]) ?? [null, null];

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
     * Index
     *
     * @param $_reload (optional) 
     *
     * @return mixed
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
            return $this->response(new HtmlResponse(
                Qore::service(TemplateRendererInterface::class)->render('app::main', [
                    'title' => 'История команд amadeus',
                    'frontProtocol' => $ig('layout')->component($component)->compose(),
                ])
            ));
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

        $report = $this->plugin(Report::class);
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
        $request = $this->model->getRequest();
        $pagination = $_data === true;

        if ($_data === true) {

            $filters = $this->model->getFilters(true);
            $queryParams = $this->model->getRequest()->getQueryParams();
            $offset = $this->limit * ((int)($queryParams['page'] ?? 1) - 1);

            /** @var Indexer */ 
            $indexer = $this->sm('AmadeusCommand:Indexer')->plugin(Indexer::class);

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

            if ($ids) {
                $gw = $this->gateway(['@this.id' => $ids]);
                $_data = $gw->all();

                $agents = $this->mm('SM:User')->where([
                    '@this.id' => $_data->indexBy('userId')->extract('userId')->filter(
                        fn($_userId) => $_userId
                    )->toList(),
                ])->all();

                $_data = Qore::collection($_data->map(function($_command) use ($agents) {
                    $_command['user'] = $agents->firstMatch(['id' => $_command['userId']]);
                    return $_command;
                }));
            } else {
                $_data = null;
            }
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
                        return ! is_null($_item->user) ? sprintf('%s (%s)', $_item->user->fullname, $_item->user->agentSign) : '';
                    }
                ],
                'officeId' => [
                    'label' => 'Офис',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
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
            'reports' => $this->plugin(Report::class)->getReports(),
            'componentActions' => [
                'report' => [
                    'icon' => 'fas fa-file-csv',
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('report'),
                        $routeResult->getMatchedParams(),
                        $this->model->getSubjectFilters()
                    ),
                ],
                'reload'
            ],
            'pagination' => $pagination ? [
                'count' => $this->getCount(),
                'page' => (int)($queryParams['page'] ?? 1),
                'per-page' => $this->limit,
            ] : null,
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
        
        /** @var Indexer */
        $queryParams = $this->model->getRequest()->getQueryParams();
        $indexer = $this->sm('AmadeusCommand:Indexer')->plugin(Indexer::class);
        $results = $indexer->search($filters, [
            'query' => $queryParams['query'] ?? null,
        ]);

        return $results->getTotal();
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        return [
            'view' => [
                'label' => 'Просмотреть команду',
                'icon' => 'fas fa-eye',
                'actionUri' => function($_data) {
                    return Qore::url(
                        $this->getRouteName('view'),
                        ['id' => $_data['id']]
                    );
                },
            ],
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

}
