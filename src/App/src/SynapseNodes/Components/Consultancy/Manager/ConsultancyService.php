<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Consultancy\Manager;

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
use Qore\App\SynapseNodes\Components\Consultancy\Consultancy;
use Qore\App\SynapseNodes\Components\Consultancy\Manager\InterfaceGateway\ConsultancyComponent;
use Qore\App\SynapseNodes\Components\ConsultancyMessage\ConsultancyMessage;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: ConsultancyService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class ConsultancyService extends ServiceArtificer
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
        $_router->group('/consultancy', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            $_router->get('/dialog/{id: \d+}', 'dialog');
            $_router->post('/message/{id: \d+}', 'message');
            $_router->get('/close/{id: \d+}', 'close');
            $_router->get('/reload-dialog/{id: \d+}', 'reload-dialog');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['dialog', 'message', 'close', 'reload-dialog' => 'reloadDialog']) ?? [null, null];

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
                'title' => 'Управление консультациями - Сервис управления консультациями',
                'frontProtocol' => $ig('layout')->component($component)->compose(),
            ])));
        }
    }

    /**
     * update
     *
     */
    protected function dialog()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();

        $ig = Qore::service(InterfaceGateway::class);

        $routeResult = $this->model->getRouteResult();
        $matchedParams = $routeResult->getMatchedParams();

        $consultancy = $this->mm()->where(['@this.id' => $matchedParams['id']])->one();

        if (! $consultancy) {
            return $this->response([]);
        }

        $dialog = $this->getDialogComponent($consultancy);

        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-consultancy'))
            ->setTitle('Диалог консультации')
            ->setOption('modal-type', 'rightside')
            ->setOption('size', 'xl')
            ->component($dialog)
            ->execute('open');

        return $this->response($ig('layout')->component($modal));
    }

    /**
     * Result dialog
     *
     * @return ?ResultInterface 
     */
    protected function reloadDialog(): ?ResultInterface
    {
        $request = $this->model->getRequest();

        $ig = Qore::service(InterfaceGateway::class);

        $routeResult = $this->model->getRouteResult();
        $matchedParams = $routeResult->getMatchedParams();

        $consultancy = $this->mm()->where(['@this.id' => $matchedParams['id']])->one();

        $dialog = $this->getDialogComponent($consultancy);

        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-consultancy'))
            ->setTitle('Диалог консультации')
            ->setOption('modal-type', 'rightside')
            ->setOption('size', 'xl')
            ->component($dialog)
            ->execute('open');

        return $this->response($ig('layout')->component($modal));
    }

    /**
     * Message
     *
     * @return ?ResultInterface
     */
    protected function message(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $matchedParams = $routeResult->getMatchedParams();

        $consultancy = $this->mm()->where(['@this.id' => $matchedParams['id']])->one();

        if (! $consultancy) {
            return $this->response([]);
        }

        $message = $request('message');

        if (! $message) {
            return $this->response([]);
        }

        $message = $this->mm('SM:ConsultancyMessage', [
            'message' => $message,
            'idConsultancy' => $consultancy['id'],
            'direction' => ConsultancyMessage::DIRECTION_OUT,
        ]);

        $consultancy->isAnswered();

        $this->mm($message)->save();
        $this->mm($consultancy)->save();

        $dialog = $this->getDialogComponent($consultancy);

        return $this->response($dialog);
    }

    /**
     * Message
     *
     * @return ?ResultInterface
     */
    protected function close(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $matchedParams = $routeResult->getMatchedParams();

        $consultancy = $this->mm()->where(['@this.id' => $matchedParams['id']])->one();

        if (! $consultancy) {
            return $this->response([]);
        }

        $consultancy->closed = 1;
        $this->mm($consultancy)->save();

        $dialog = $this->getDialogComponent($consultancy);

        return $this->response($dialog);
    }

    /**
     * Get dialog component
     *
     * @param \Qore\App\SynapseNodes\Components\Consultancy\Consultancy $_consultancy 
     *
     * @return \Qore\App\SynapseNodes\Components\Consultancy\Manager\InterfaceGateway\ConsultancyComponent
     */
    protected function getDialogComponent(Consultancy $_consultancy): ConsultancyComponent
    {
        $messages = $this->mm('SM:ConsultancyMessage')
            ->where(['@this.idConsultancy' => $_consultancy['id']])
            ->select(fn ($_select) => $_select->order('@this.id'))
            ->all();

        $ig = Qore::service(InterfaceGateway::class);
        $dialog = $ig(ConsultancyComponent::class, sprintf('%s.%s', get_class($this), 'dialog-consultancy'))
            ->setOption('consultancy', $_consultancy->toArray(true))
            ->setOption('message-route', Qore::url($this->getRouteName('message'), ['id' => $_consultancy['id']]))
            ->setOption('close-route', Qore::url($this->getRouteName('close'), ['id' => $_consultancy['id']]))
            ->setOption('reload-dialog-route', Qore::url($this->getRouteName('reload-dialog'), ['id' => $_consultancy['id']]))
            ->setOption('messages', $messages->map(fn($_message) => $_message->toArray(true))->toList());

        return $dialog;
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
        if ($_data !== null) {

            $gw = $this->mm()
                ->select(fn ($_select) => $_select->order('@this.__updated desc'));
            $_data = $gw->all();
        }

        return $this->presentAs(ListComponent::class, [
            'columns' => [
                'row:class' => [
                    'transform' => function ($_item) {
                        return (int)$_item->isUpdated == 1 ? 'bg-info-light' : '';
                    },
                ],
                'id' => [
                    'label' => '#',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                ],
                'question' => [
                    'label' => 'Запрос',
                    'class-header' => 'col-4',
                    'class-column' => 'col-4',
                ],
                'token' => [
                    'label' => 'Сессия',
                    'class-header' => 'col-3',
                    'class-column' => 'col-3',
                ],
                'closed' => [
                    'label' => 'Статус',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                    'transform' => function ($_item) {
                        return isset($_item['closed']) && (int)$_item['closed']
                            ? ['isLabel' => true, 'class' => 'bg-warning-light text-warning', 'label' => 'Закрыта']
                            : ['isLabel' => true, 'class' => 'bg-info-light text-info', 'label' => 'Открыта'];
                    },
                ],
                'created' => [
                    'label' => 'Создан',
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
        return [
            'dialog' => [
                'label' => 'Dialogs',
                'icon' => 'fas fa-bars',
                'actionUri' => function($_data) {
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('dialog'),
                        ['id' => $_data['id']],
                    );
                },
            ],
            'delete',
        ];

        // return [];
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
