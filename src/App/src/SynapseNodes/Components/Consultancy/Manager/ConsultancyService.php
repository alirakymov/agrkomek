<?php
declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Consultancy\Manager;

use Laminas\Db\Sql\Predicate\NotIn;
use Mezzio\Helper\UrlHelper;
use Qore\App\SynapseNodes\Components\Moderator\Moderator;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Decorator\QoreFront;
use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Mezzio\Template\TemplateRendererInterface; use Laminas\Diactoros\Response\HtmlResponse;
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
            $_router->post('/moderator/{id: \d+}', 'moderator');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['dialog', 'message', 'close', 'moderator' => 'moderator', 'reload-dialog' => 'reloadDialog']) ?? [null, null];

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
     * @param  $_reload (optional) 
     *
     * @return null|ResultInterface
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

        $consultancy = $this->mm()->with('moderator')->where(['@this.id' => $matchedParams['id']])->one();

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
     * update
     *
     */
    protected function moderator()
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

        $moderator = $this->mm('SM:Moderator')->where(['@this.id' => $request('id')])->one();
        if (! $moderator) {
            return $this->response([]);
        }

        $consultancy->link('moderator', $moderator);
        $this->mm($consultancy)->save();

        return $this->response([]);
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

        $consultancy = $this->mm()->with('moderator')->where(['@this.id' => $matchedParams['id']])->one();

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
        $this->mm($_consultancy)->with('moderator')->one();

        $messages = $this->mm('SM:ConsultancyMessage')
            ->where(['@this.idConsultancy' => $_consultancy['id']])
            ->select(fn ($_select) => $_select->order('@this.id'))
            ->all();

        $consultanices = $this->mm('SM:Consultancy')
            ->where(['@this.token' => $_consultancy->token])
            ->all()
            ->filter(fn($_item) => $_item['id'] != $_consultancy->id)
            ->extract('id')
            ->toList();

        $otherMessages = $this->mm('SM:ConsultancyMessage')
            ->where([
                '@this.idConsultancy' => $consultanices
            ])
            ->select(fn ($_select) => $_select->order('@this.id'))
            ->all();

        $moderator = $_consultancy->moderator();
        unset($moderator['password']);

        $moderators = $this->mm('SM:Moderator')
            ->with('role', function($_gw) {
                $_gw->with('permissions');
            })
            ->where(['@this.role.permissions.component' => ConsultancyService::class])
            ->all();

        $moderators = $moderators->map(function($_moderator) {
            unset($_moderator['password']);
            unset($_moderator['otp']);
            return $_moderator->toArray(true);
        })->toList();

        $ig = Qore::service(InterfaceGateway::class);
        $dialog = $ig(ConsultancyComponent::class, sprintf('%s.%s', get_class($this), 'dialog-consultancy'))
            ->setOption('consultancy', $_consultancy->toArray(true))
            ->setOption('moderators', $moderators)
            ->setOption('message-route', Qore::url($this->getRouteName('message'), ['id' => $_consultancy['id']]))
            ->setOption('close-route', Qore::url($this->getRouteName('close'), ['id' => $_consultancy['id']]))
            ->setOption('moderator-route', Qore::url($this->getRouteName('moderator'), ['id' => $_consultancy['id']]))
            ->setOption('reload-dialog-route', Qore::url($this->getRouteName('reload-dialog'), ['id' => $_consultancy['id']]))
            ->setOption('messages', $messages->map(fn($_message) => $_message->toArray(true))->toList())
            ->setOption('otherMessages', $otherMessages->map(fn($_message) => $_message->toArray(true))->toList());

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
            $request = $this->model->getRequest();

            /**@var Moderator */
            $moderator = $request->getAttribute(Moderator::class);
            $admin = $request->getAttribute('admin');

            $gw = $this->mm()->with('category')->with('moderator')
                ->select(fn ($_select) => $_select->order('@this.__updated desc'));

            $filters = [];
            if (is_null($admin)) {
                $permission = $moderator->getPermission(ConsultancyService::class);
                if ($permission->extra) {
                    $filters['@this.category.id'] = $permission->extra;
                }

                if ($filters) {
                    $gw->where($filters);
                }
            }

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
                    'class-header' => 'col-3',
                    'class-column' => 'col-3',
                    'transform' => function ($_item) {
                        return sprintf('<small class="fw-light">%s</small> <br> %s', $_item['token'], $_item['question']);
                    },
                ],
                'moderator' => [
                    'label' => 'Консультант',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2 text-center',
                    'transform' => function ($_item) {
                        return $_item->moderator() ? sprintf('%s %s', $_item->moderator()->firstname, $_item->moderator()->lastname) : 'Не назначен';
                    },
                ],
                'category text-center' => [
                    'label' => 'Категория',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function ($_item) {
                        $category = $_item->category();
                        return $category ? $category->title : 'Не назначена';
                    },
                ],
                'closed' => [
                    'label' => 'Статус',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                    'transform' => function ($_item) {
                        return isset($_item['closed']) && (int)$_item['closed']
                            ? ['isLabel' => true, 'class' => 'bg-warning-light text-warning', 'label' => 'Закрыта']
                            : ['isLabel' => true, 'class' => 'bg-primary-light text-white', 'label' => 'Открыта'];
                    },
                ],
                'updated' => [
                    'label' => 'Последняя активность',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function($_item) {
                        return $_item['__updated']->format('d.m.Y H:i');
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
