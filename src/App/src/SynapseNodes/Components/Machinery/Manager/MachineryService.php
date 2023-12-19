<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Machinery\Manager;

use Mezzio\Helper\UrlHelper;
use Qore\App\SynapseNodes\Components\Machinery\Machinery;
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
use Qore\App\SynapseNodes\Components\Machinery\Manager\InterfaceGateway\Form;
use Qore\ORM\Sql\Select;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: MachineryService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class MachineryService extends ServiceArtificer
{
    /**
     * sortable
     *
     * @var mixed
     */
    private $sortable = false;

    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = 'MachineryForm';

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
        $_router->group('/machinery', null, function($_router) {
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
                'title' => 'Управление объявлениями - Управление объявлениями',
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
     * create
     *
     */
    protected function create()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();


        $ig = Qore::service(InterfaceGateway::class);

        $machinery = $this->mm([]);

        /**@var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);
        $form = $ig(Form::class, sprintf('machinery-form-%s', 'new'));

        $form->setOption('machinery', [
            'images' => [],
            'params' => [],
        ]);

        $form->setOption('upload-route', Qore::url($this->sm('ImageStore:Uploader')->getRouteName('upload')));
        $form->setOption('save-route', Qore::url($this->getRouteName('create')));
        $form->setOption('types', Machinery::getTypes());

        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-create'))
            ->setTitle('Создание')
            ->setOption('modal-type', 'rightside')
            ->setOption('size', 'xl')
            ->component($form);

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody()['machinery'];
            unset($data['__created'], $data['__updated']);

            $entity = $this->mm($data);
            $this->mm($entity)->save();

            $component = $this->getComponent();
            # - Generate json response
            return $this->response([
                $modal->execute('close'),
                $component->execute('reload'),
            ]);
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

        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $machinery = $this->mm()->where(['@this.id' => $routeParams['id']])->one();
        if (! $machinery) {
            return $this->response([]);
        }

        /**@var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);
        $form = $ig(Form::class, sprintf('machinery-form-%s', $machinery->id));

        $form->setOption('machinery', $machinery->toArray(true));
        $form->setOption('upload-route', Qore::url($this->sm('ImageStore:Uploader')->getRouteName('upload')));
        $form->setOption('save-route', Qore::url($this->getRouteName('update')));
        $form->setOption('types', Machinery::getTypes());
        $form->setOption('statuses', Machinery::getStatuses());

        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-update'))
            ->setTitle('Редактирование')
            ->setOption('modal-type', 'rightside')
            ->setOption('size', 'xl')
            ->component($form);

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody()['machinery'];
            unset($data['__created'], $data['__updated']);

            $entity = $this->mm($data);
            $this->mm($entity)->save();

            $component = $this->getComponent(null);
            # - Generate json response
            return $this->response([
                $modal->execute('close'),
                $component->execute('reload'),
            ]);
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

        $component = $this->getComponent(null);
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
        # - Формируем уникальный суффикс для имени компонента интерфейса
        if ($_data !== null) {

            $gw = $this->mm()
                ->select(fn ($_select) => $_select->order('@this.__updated desc'));

            $queryParams = $this->model->getRequest()->getQueryParams();

            if (isset($queryParams['user-id'])) {
                $gw->with('user')->where([
                    '@this.user.id' => $queryParams['user-id'],
                ]);
            }

            $_data = $gw->all();
        }

        return $this->presentAs(ListComponent::class, [
            'columns' => [
                'id' => [
                    'label' => '#',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                ],
                'title' => [
                    'label' => 'Название',
                    'class-header' => 'col-4',
                    'class-column' => 'col-4',
                ],
                'price' => [
                    'label' => 'Цена',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                ],
                'status' => [
                    'label' => 'Статус',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2 text-center',
                    'transform' => function ($_item) {
                        if (! $_item['status']) {
                            return ['isLabel' => true, 'class' => 'bg-warning-light text-warning', 'label' => 'Не назначен'];
                        }

                        switch(true) {
                            case $_item['status'] === Machinery::STATUS_CHECKING:
                                return ['isLabel' => true, 'class' => 'bg-warning-light text-warning', 'label' => 'На проверке'];
                            case $_item['status'] === Machinery::STATUS_REJECTED:
                                return ['isLabel' => true, 'class' => 'bg-danger-light text-danger', 'label' => 'Отклонено'];
                            case $_item['status'] === Machinery::STATUS_ACTIVE:
                                return ['isLabel' => true, 'class' => 'bg-info-light text-info', 'label' => 'Активно'];
                            case $_item['status'] === Machinery::STATUS_ARCHIVE:
                                return ['isLabel' => true, 'class' => 'bg-warning-light text-danger', 'label' => 'В архиве'];
                        }
                    },
                ],
                'type' => [
                    'label' => 'Тип',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1 text-center',
                    'transform' => function ($_item) {
                        $types = Machinery::getTypes();

                        $type = Qore::collection($types)->firstMatch(['id' => $_item['type']]);

                        if (is_null($type)) {
                            return ['isLabel' => true, 'class' => 'bg-warning-light text-warning', 'label' => 'Неопределен'];
                        }

                        return ['isLabel' => true, 'class' => 'bg-info-light text-info', 'label' => $type['label']];
                    },
                ],
                'created' => [
                    'label' => 'Создано',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function($_item) {
                        return $_item['__created']->format('d.m.Y H:i');
                    }
                ],
            ],
            'actions' => $this->getListActions(),
            'suffix' => $testFilters['filters']['id'] ?? null,
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
            $optionsStorage = $this->getLocalGateway(['id' => (string)$this->requestFilters['__idparent']])->one();
        } else  {
            /**
                $filter = $this->model->getFilters(true)->firstMatch([
                    'namespace' => sprintf('%s.%s', $this->getNameIdentifier(), '{RelatedSynapse:Service}'),
                ]);

                if (isset($filter['filters']['id'])) {
                    $optionsStorage = $this->mm('{RelatedSynapse}')->where(function($_where) use ($filter) {
                        $_where(['id' => (string)$filter['filters']['id']]);
                    })->one();
                }
            */
        }

        return $optionsStorage;
    }

}
