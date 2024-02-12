<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Manager;

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
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\Filter\Filter;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\Indexer\Indexer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: UserService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class UserService extends ServiceArtificer
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
    private $serviceForm = 'UserForm';

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
        $_router->group('/user', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            $_router->get('/reset-password/{id:\d+}', 'resetPassword');
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
        list($method, $arguments) = $this->routingHelper->dispatch([ 'resetPassword' ]) ?? [null, null];

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
                'title' => 'Сервис управления пользователями - Сервис управления пользователями',
                'frontProtocol' => $ig('layout')->component($component)->compose(),
            ])));
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

        return $this->response();
    }

    /**
     * Reset password
     *
     * @return ResultInterface
     */
    protected function resetPassword()
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $component = $this->getComponent();

        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $user = $this->mm()->where(fn($_where) => $_where(['@this.id' => $routeParams['id']]))->one();
        if ($user) {
            $user->resetPassword()->generateOtp();
            $this->mm($user)->save();
        }

        # - Generate json response
        return $this->response([ $component->execute('reload'), ]);
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
        $pagination = $_data === true;

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
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                ],
                'phone' => [
                    'label' => 'Телефон',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                ],
                'fullname' => [
                    'label' => 'Фамилия Имя',
                    'class-header' => 'col',
                    'class-column' => 'col',
                    'transform' => function($_item) {
                        return $_item['fullname'] ? htmlentities(sprintf('%s (%s)', $_item['fullname'], $_item['username'])) : '';
                    }
                ],
                'otp' => [
                    'label' => 'Код',
                    'class-header' => 'text-center align-middle',
                    'class-column' => 'text-center align-middle',
                    'transform' => function ($_item) {
                        return isset($_item['otp'])
                            ? ['isLabel' => true, 'class' => 'bg-success-light text-success', 'label' => $_item['otp']]
                            : ['isLabel' => true, 'class' => 'bg-info-light text-info', 'label' => 'Не активeн'];
                    }
                ],
                'created' => [
                    'label' => 'Создан',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function($_item) {
                        return ($_item['__created'] ??= $_item['__updated'])
                            ->format('d.m.Y H:i');
                    }
                ],
            ],
            'actions' => $this->getListActions(),
            'suffix' => $testFilters['filters']['id'] ?? null,
            'sortable' => $this->getSortableOptions(),
            'filter-form' => $filterForm,
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
        $amadeusSession = $this->sm('AmadeusSession:Manager');
        $amadeusCommand = $this->sm('AmadeusCommand:Manager');
        return [
            'amadeus-command' => [
                'label' => 'Команды amadeus',
                'icon' => 'fas fa-terminal',
                'actionUri' => function($_data) use ($amadeusCommand) {
                    return Qore::service(UrlHelper::class)->generate(
                        $amadeusCommand->getRouteName('index'),
                        [],
                        $amadeusCommand->getFilters(null, ['userId' => $_data['id']])
                    );
                },
            ],
            'amadeus-session' => [
                'label' => 'Сессии amadeus',
                'icon' => 'fas fa-laptop',
                'actionUri' => function($_data) use ($amadeusSession) {
                    return Qore::service(UrlHelper::class)->generate(
                        $amadeusSession->getRouteName('index'),
                        [],
                        $amadeusSession->getFilters('User:Connector', ['id' => $_data['id']])
                    );
                },
            ],
            'resetPassword' => [
                'label' => 'Сбросить пароль',
                'icon' => 'fas fa-key',
                'actionUri' => function($_data) {
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('resetPassword'),
                        ['id' => $_data['id']],
                    );
                },
                'confirm' => function($_data) {
                    return [
                        'title' => 'Сброс пароля',
                        'message' => sprintf('Точно сбрасываем пароль пользователю "%s"?', $_data['fullname'] ?? $_data['title'] ?? $_data->id)
                    ];
                },
            ],
            'update', 'delete',
        ];

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
