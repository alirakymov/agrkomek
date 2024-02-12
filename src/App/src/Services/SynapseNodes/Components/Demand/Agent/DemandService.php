<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Demand\Agent;

use DateTime;
use Laminas\Db\Sql\Expression;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\EmailAddress;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\In;
use Manticoresearch\Query\QueryString;
use Manticoresearch\Search;
use Mezzio\Helper\UrlHelper;
use Qore\App\Services\Deferring\DeferringInterface;
use Qore\App\Services\Tracking\TrackingInterface;
use Qore\App\SynapseNodes\Components\DemandAttachments\DemandAttachments;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandAssigneeExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandAttachmentsExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandRoutesExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandStatusExtension;
use Qore\App\SynapseNodes\Components\Demand\DemandExtenderInterface;
use Qore\App\SynapseNodes\Components\Operation\Operation;
use Qore\App\SynapseNodes\Components\Partner\Partner;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\Collection\CollectionInterface;
use Qore\DealingManager\ResultInterface;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandEventsExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandFollowersExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandGroupExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\InterfaceGateway\DemandComponent;
use Qore\App\SynapseNodes\Components\Demand\Agent\InterfaceGateway\DemandListComponent;
use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\Form\Decorator\QoreFront;
use Qore\InterfaceGateway\Component\Modal;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandPartnerExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandPartnerOnlyExtension;
use Qore\App\SynapseNodes\Components\Demand\Agent\Extender\DemandProductStagesExtension;
use Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage;
use Qore\App\SynapseNodes\Components\DemandStatus\DemandStatus;
use Qore\App\SynapseNodes\Components\Product\Product;
use Qore\App\SynapseNodes\Components\UserGroup\UserGroup;
use Qore\Collection\Collection;
use Qore\DealingManager\Model;
use Qore\ORM\Gateway\Gateway;
use Qore\ORM\Gateway\GatewayInterface;
use Qore\ORM\ModelManager;
use Qore\ORM\Sql\Where;
use Qore\SynapseManager\Plugin\Indexer\Indexer;
use Qore\SynapseManager\SynapseManager;

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
            $_router->get('', 'index');
            $_router->get('/reload', 'reload');
            $_router->get('/view/{id:\d+}', 'view');
            $_router->post('/upload/{id:\d+}', 'upload');
            $_router->any('/create', 'create');
            $_router->post('/save/{id:\d+}', 'save');
            $_router->any('/set-status/{id:\d+}', 'set-status');
            $_router->any('/comment/{id:\d+}', 'comment');
            $_router->any('/new-message/{id:\d+}', 'message');
            $_router->any('/merge', 'merge');

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
        $request = $this->model->getRequest();
        /** @var User */
        $user = $request->getAttribute(User::class);
        # - Подгружаем группы пользователя, в которых он участвует
        $this->mm($user)->with('groups')->one();

        $routeResult = $this->model->getRouteResult();
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        list($method, $arguments) = $this->routingHelper->dispatch(['view', 'save', 'upload', 'merge']) ?? ['notFound', null];

        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @param $_reload (optional) 
     *
     * @return ?ResultInterface
     */
    protected function index($_reload = false) : ?ResultInterface { 
        $request = $this->model->getRequest();
        /** @var User */
        $user = $request->getAttribute(User::class);

        $component = $this->getComponent(true);
        $ig = Qore::service(InterfaceGateway::class);

        if ($request->isXmlHttpRequest()) {
            return $this->response(
                [ $_reload ? $component : $ig('layout')->component($component) ]
            );
        } else {
            return $this->response(new HtmlResponse(
                Qore::service(TemplateRendererInterface::class)->render('frontapp::erp-platform/cabinet.twig', [
                    'title' => 'Кабинет агента',
                    'interface-gateway' => $ig(Layout::class, 'layout')
                        ->component($component)
                        ->compose(),
                ])
            ));
        }
    }

    /**
     * Create action
     *
     * @return ?ResultInterface
     */
    protected function create(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        /** @var User */
        $user = $request->getAttribute(User::class);

        $ig = Qore::service(InterfaceGateway::class);


        /** @var FormMaker */
        $formMaker = $this->plugin(FormMaker::class);
        $fm = $formMaker->make('DemandForm');
    
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-create'))
            ->setTitle('Новая заявка')
            ->type(Modal::RIGHTSIDE)
            ->size(Modal::SIZE_LG)
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                # - Save data
                $this->mm($demand = $this->model->getDataSource()->extractData()->first())->save();
                # - Generate json response
                $component = $this->getComponent(true);
                return $this->response([
                    $modal->execute('close'),
                    $component->run('reaction', ['actionUri' => Qore::url($this->getRouteName('view'), ['id' => $demand['id']])]),
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
     * View demand details
     *
     * @return ?ResultInterface 
     */
    protected function view(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        /** @var User */
        $user = $request->getAttribute(User::class);

        /** @var Demand */
        $demand = $this->gateway()->where(function($_where) use ($routeParams) {
            $_where(['@this.id' => $routeParams['id']]);
        })->one();

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        # - Fire demand message register event
        $tracking->postpone(Operation::OPERATION_DEMAND_OPEN, $demand, [
            'user' => $user
        ]);

        Qore::service(DemandExtenderInterface::class)->with([
            DemandStatusExtension::class,
            DemandPartnerExtension::class,
            DemandRoutesExtension::class,
            DemandAssigneeExtension::class,
            DemandGroupExtension::class,
            DemandFollowersExtension::class,
            DemandAttachmentsExtension::class,
            DemandProductStagesExtension::class,
            DemandEventsExtension::class => ['limit' => 100],
        ])->populate($demand);

        # - Get all statuses
        $statuses = $this->mm('SM:DemandStatus')->all();
        # - Get all partners 
        $partners = $this->mm('SM:Partner')->all();
        # - Get all assignees/users 
        $assignees = $this->getAssignees();
        # - Get all groups 
        $groups = $this->getGroups();
        # - Get all products with stages 
        $products = $this->getProducts();
        
        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);

        /** @var DemandComponent */
        $component = $ig(DemandComponent::class, sprintf('%s.%s', get_class($this), $demand->formatUnique()))
            ->setOption(
                'demand', 
                $demand->prepare()
            )->setOption(
                'status-list', 
                $statuses->map(fn($_status) => $_status->toArray(true))->toList()
            )->setOption(
                'partner-list', 
                $partners->map(fn($_partner) => $_partner->toArray(true))->toList()
            )->setOption(
                'assignee-list', 
                $assignees->map(fn($_assignee) => $_assignee->extract(['id','fullname', 'username']))->toList()
            )->setOption(
                'group-list', 
                $groups->map(fn($_group) => $_group->extract(['id','title', 'description']))->toList()
            )->setOption(
                'product-list', 
                $products->map(fn($_product) => $_product->toArray(true))->toList()
            )->setOption(
                'user-id',
                $user->id
            );

        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-view'))
            ->setTitle(sprintf('Просмотр заявки %s', $demand->formatUnique()))
            ->type(Modal::RIGHTSIDE)
            ->size(Modal::SIZE_XL)
            ->component($component)
            ->execute('open');

        $modal->panel([
            [
                'label' => '',
                'icon' => 'far fa-save',
                'action' => [
                    'component' => $component->getName(),
                    'command' => 'save',
                    'options' => [],
                ],
            ],
            [
                'label' => '',
                'icon' => 'fas fa-bars',
                'submenu' => [
                    [
                        'label' => 'Назначить исполнителя',
                        'icon' => 'fas fa-user-circle',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'SetAssignee',
                            'options' => [],
                        ],
                    ],
                    [
                        'label' => 'Назначить группу',
                        'icon' => 'fas fa-user-friends',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'SetGroup',
                            'options' => [],
                        ],
                    ],
                    [
                        'label' => 'Назначить клиента',
                        'icon' => 'far fa-building',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'setPartner',
                            'options' => [],
                        ],
                    ],
                    [
                        'label' => 'Назначить подписчиков',
                        'icon' => 'fas fa-users',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'setFollowers',
                            'options' => [],
                        ],
                    ],
                    [
                        'label' => 'Назначить услуги',
                        'icon' => 'fas fa-plane',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'setProductStage',
                            'options' => [],
                        ],
                    ],
                    [
                        'label' => 'Добавить комментарий',
                        'icon' => 'far fa-comment-dots',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'newComment',
                            'options' => [],
                        ],
                    ],
                    [
                        'label' => 'Новое сообщение',
                        'icon' => 'far fa-envelope',
                        'action' => [
                            'component' => $component->getName(),
                            'command' => 'newMessage',
                            'options' => [],
                        ],
                    ],
                ],
            ],
        ]);

        return $this->response($ig('layout')->component($modal));
    }

    /**
     * Save demand data
     *
     * @return ?ResultInterface 
     */
    protected function save(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        # - Get connection instance
        $connection = $mm->getAdapter()->getDriver()->getConnection();

        /** @var Demand */
        $demand = $this->gateway()->where(function($_where) use ($routeParams) {
            $_where(['@this.id' => $routeParams['id']]);
        })->with('status')->with('partner')->with('followers')
            ->with('assignee')->with('productStages')
            ->one();

        if (! $demand) {
            return $this->response([]);
        }

        $data = [];
        foreach (['title'] as $attr) {
            if (($value = $request(sprintf('demand.%s', $attr, null))) && $demand[$attr] !== $value) {
                $data[$attr] = $value;
            }
        }

        # - Merge demand with request data
        $demand->merge($data);
        # - Get all statuses 
        $statuses = $this->mm('SM:DemandStatus')->all();
        # - Get status from request
        $status = $request('demand.status', []);
        $status = ! is_array($status) ? [] : $status;

        # - Если был назначен новый статус
        if (isset($status['id']) && (! $demand->status() || (int)$demand->status()->id !== (int)$status['id'])) {
            # - Находим статус в списке
            if( $status = $statuses->firstMatch(['id' => $status['id']])) {
                # - Назначаем новый статус 
                $status && $demand->setStatus($status);
            }
        }

        # - Set assignee 
        $assignee = $request('newAssignee');
        if ($assignee) {
            $user = $this->mm('SM:User')->where(function ($where) use ($assignee) {
                $where(['@this.id' => $assignee]);
            })->one();
            
            $user && $demand->setAssignee($user);
        }

        # - Set group 
        $group = $request('newGroup');
        if ($group) {
            $group = $this->mm('SM:UserGroup')->where(function ($where) use ($group) {
                $where(['@this.id' => $group]);
            })->one();
            
            $group && $demand->setGroup($group);
        }

        # - New comment
        $comment = $request('comment');
        if ($comment) {
            $demand->setComment($comment);
        }

        # - Get all partners 
        $partners = $this->mm('SM:Partner')->all();
        # - Set partner 
        $partner = $request('newPartner');
        if ($partner && $partner = $partners->firstMatch(['id' => $partner])) {
            $demand->setPartner($partner);
        }

        # - Disable events SLA tracking
        $disabledSla = $request('disabledSla');
        if ($disabledSla) {
            $events = $this->mm('SM:DemandEvent')
                ->where([
                    '@this.id' => $disabledSla,
                    '@this.slaTarget' => 0,
                ])->all();

            $events->each(fn($_event) => $_event['slaDate'] = 0)->compile();
            $mm($events)->save();

            /** @var TrackingInterface */
            $tracking = Qore::service(TrackingInterface::class);
            # - Fire sla changed event
            $tracking->fire(Demand::DEMAND_SLA_CHANGED, $demand);
        }

        # - Set followers 
        $newFollowers = $request('newFollowers');
        if ($newFollowers) {
            $newFollowers = $this->mm('SM:User')->where(function ($where) use ($newFollowers) {
                $where(['@this.id' => $newFollowers]);
            })->all();

            foreach ($newFollowers as $follower) {
                if (! $demand->followers->contains($follower)) {
                    $demand->setFollowers($newFollowers);
                    break;
                }
            }
        }
        # - unset followers 
        $unsetFollowers = $request('unsetFollowers');
        if ($unsetFollowers) {
            $unsetFollowers = $this->mm('SM:User')->where(function ($where) use ($unsetFollowers) {
                $where(['@this.id' => $unsetFollowers]);
            })->all();
            $demand->unsetFollowers($unsetFollowers);
        }

        # - ProductStages
        $productStages = $request('productStages');
        if (! is_null($productStages)) {
            $demand->setProductStages($productStages);
        }

        # - New message
        $message = $request('message');
        if (! is_null($demand->partner()) && isset($message['emails'], $message['subject'], $message['body'])) {   
            $partner = $this->mm($demand->partner())->with('emails')->with('outboxEmails')->one();
            $emails = $this->getOutboxPartnerEmails($partner, $message['emails']);

            if ($emails) {
                $demandMessage = $mm('SM:DemandMessage', [
                    'isSend' => true,
                    'subject' => $message['subject'],
                    'body' => $message['body'],
                    'data' => null,
                    'to' => $emails,
                    'direction' => DemandMessage::OUTBOX,
                    'replyMessageId' => $message['replyId'] ?? null,
                    'slaTarget' => $message['slaTarget'] ?? null,
                    'stages' => $productStages ?? [],
                ]);

                if (isset($message['attachments']) && $message['attachments']) {
                    $attachments = $mm('SM:DemandAttachment')->where(['@this.id' => $message['attachments']])->all();
                    if ($attachments->count()) {
                        $demandMessage->link('attachments', $attachments);
                    }
                }

                $demand->setMessage($demandMessage);
            }
        }

        $mm = Qore::service(ModelManager::class);
        $connection = $mm->getAdapter()->getDriver()->getConnection();
        $connection->execute('BEGIN');
        # - Save demand [!No move saving]
        $this->mm($demand)->save();
        $connection->execute('COMMIT');

        Qore::service(DemandExtenderInterface::class)->with([
            DemandStatusExtension::class,
            DemandPartnerExtension::class,
            DemandRoutesExtension::class,
            DemandAssigneeExtension::class,
            DemandGroupExtension::class,
            DemandAttachmentsExtension::class,
            DemandEventsExtension::class => ['limit' => 100],
        ])->populate($demand);

        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);
        /** @var DemandComponent */
        $component = $ig(DemandComponent::class, sprintf('%s.%s', get_class($this), $demand->formatUnique()))
            ->setOption('demand', $demand->toArray(true));

        return $this->response($component);
    }

    /**
     * Save demand data
     *
     * @return ?ResultInterface 
     */
    protected function merge(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        if (! $request('demands', false)) {
            return $this->response([]);
        }

        $demands = $this->mm('SM:Demand')->select(function($_select) {
            $_select->order('@this.__created');
        })->where(['@this.id' => $request('demands', [])])->all();

        if (! $demands->count()) {
            return $this->response([]);
        }

        $targetDemand = $demands->first();

        $demands = $demands->skip(1)->each(fn($_demand) => $_demand['__idparent'] = $targetDemand['id'])->compile();

        $this->mm($demands)->save();

        /** @var DeferringInterface */
        $deferring = Qore::service(DeferringInterface::class);
        # - Отправка письма клиенту
        $deferring->defer(function(Demand $_demand, CollectionInterface $_demands) {
            try {

                /** @var ModelManager */
                $mm = Qore::service(ModelManager::class);

                $mm($_demand)->with('productStages')->all();
                $mm($_demands)->with('attachments')->with('productStages')->all();

                $attachments = $stages = [];
                foreach ($_demands as $demand) {
                    $attachments = array_merge($attachments, $demand->attachments()->toList());
                    $stages = array_merge($stages, $demand->productStages()->map(fn($_stage) => $_stage->id)->toList());
                }

                foreach ($attachments as $attachment) {
                    $attachment->unlink('demand', '*');
                    $attachment->link('demand', $_demand);
                }

                if ($attachments) {
                    $mm(Qore::collection($attachments))->save();
                }

                if ($stages) {
                    $_demand->setProductStages($stages);
                    $mm($_demand)->save();
                }

                $events = $mm('SM:DemandEvent')->with('demand')->where(['@this.demand.id' => $_demands->extract('id')->toList()])->all();

                if ($events->count()) {
                    $events->each(function($_event) use ($_demand) {
                        $_event->unlink('demand', '*');
                        $_event->link('demand', $_demand);
                        $_event['idemand'] = $_demand['id'];
                    })->compile();

                    $mm($events)->save();
                }

                return true;

            } catch (Throwable $e) {
                dump($e);
                return true;
            }

        }, [$targetDemand, $demands]);

        return $this->response([$this->getComponent(true)]);
    }

    /**
     * Upload
     *
     *
     * @return ?ResultInterface
     */
    protected function upload(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        /** @var User */
        $user = $request->getAttribute(User::class);
        $ig = Qore::service(InterfaceGateway::class);

        /** @var Demand */
        $demand = $this->gateway()->with('status')->with('partner')->where(function($_where) use ($routeParams) {
            $_where(['@this.id' => $routeParams['id']]);
        })->with('partner')->with('assignee')->one();

        if (! $demand) {
            return $this->response([]);
        }

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        if ($request->getMethod() === 'POST') {
            # - Upload files
            $files = $this->model->getRequest()->getUploadedFiles();

            foreach ($files as $file) {
                /** @var DemandAttachments */
                $demandAttachment = $mm('SM:DemandAttachment', []);
                $demandAttachment->fromUploadedFile($file);
                $demandAttachment->link('demand', $demand);
                $mm($demandAttachment)->save();

                # - Generate json response
                return $this->response(new JsonResponse([
                    'attachment' => $demandAttachment->toArray(true),
                ]));
            }
        }

        return $this->response([]);
    }

    /**
     * Not Found
     *
     * @return ?ResultInterface
     */
    protected function notFound() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Not Found', 404));
    }

    /**
     * getComponent
     *
     * @param mixed $_data
     */
    protected function getComponent($_data = null)
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        $count = is_countable($_data) ? count($_data) : 0;

        if ($_data === true) {

            $result = $this->search()->get();
            $count = $result->getTotal();
            $ids = Qore::collection($result)->map(fn($_item) => $_item->getId())->toList();

            $gw = $this->mm()->where(['@this.id' => $ids]);
            $this->applySorting($gw);

            $_data = $gw->all();


            $_data = Qore::collection(Qore::service(DemandExtenderInterface::class)->with([
                DemandAssigneeExtension::class,
                DemandGroupExtension::class,
                DemandStatusExtension::class,
                DemandPartnerOnlyExtension::class,
                DemandRoutesExtension::class,
                // DemandAttachmentsExtension::class,
                DemandProductStagesExtension::class,
            ])->populate($_data));
        }

        # - Get all statuses
        $statuses = $this->getStatuses();
        # - Get all partners 
        $partners = $this->getPartners();
        # - Get all assignees/users 
        $assignees = $this->getAssignees();
        # - Get all groups 
        $groups = $this->getGroups();
        # - Get all Products with stages
        $products = $this->getProducts();

        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);

        /** @var DemandListComponent */
        $component = $ig(DemandListComponent::class, 'agent-demands-list');
        return $component->setOption('title', 'Список заявок')
            ->setDemands($_data)
            ->setOption('statuses', $statuses->map(fn($_item) => $_item->toArray(true))->toList())
            ->setOption('partners', $partners->map(fn($_item) => $_item->toArray(true))->toList())
            ->setOption('assignees', $assignees->map(fn($_item) => $_item->extract(['id', 'fullname']))->toList())
            ->setOption('groups', $groups->map(fn($_item) => $_item->toArray(true))->toList())
            ->setOption('products', $products->map(fn($_item) => $_item->toArray(true))->toList())
            ->setOption('pagination', [
                'count' => $count,
                'page' => (int)($queryParams['page'] ?? 1),
                'per-page' => $this->limit,
                'url' => Qore::url($this->getRouteName('index'), [], $queryParams),
            ])->setOption('routes', [
                'reload' => Qore::url($this->getRouteName('reload'), [], $queryParams),
                'clean-reload' => Qore::url($this->getRouteName('reload')),
                'merge' => Qore::url($this->getRouteName('merge'), [], $queryParams),
            ])->setOption('actions', [
                'reload' => [
                    'icon' => 'fa fa-sync',
                    'actionUri' => Qore::url($this->getRouteName('reload'), [], $queryParams),
                ],
            ]);
    }

    /**
     * Calculate count of demands
     *
     * @return int 
     */
    protected function getCount() : int
    {
        $gw = $this->mm()->select(function($_select) {
            $_select->columns(['@this.id' => 'id'], true, false)
                ->group('@this.id');
        })->where(['@this.__idparent' => 0]);

        $this->applyFilters($gw);

        $result = (int)$this->mm()->select(function($_select) {
            $_select->columns(['@this.count' => new Expression('count(*)')])
                ->limit(1);
        })->where(fn($_where) => $_where->in('@this.id', $gw->buildSelect()))->all()->extract('count')->first();

        return $result;
    }

    /**
     * Get statuses list
     *
     * @return \Qore\Collection\CollectionInterface<DemandStatus>
     */
    protected function getStatuses(): CollectionInterface
    {
        return $this->mm('SM:DemandStatus')->all();
    }

    /**
     * Get partners list
     *
     * @return \Qore\Collection\CollectionInterface<Partner>
     */
    protected function getPartners(): CollectionInterface
    {
        return $this->mm('SM:Partner')->all();
    }

    /**
     * Get assignees list
     *
     * @param \Qore\ORM\Gateway\GatewayInterface $_gw
     *
     * @return \Qore\ORM\Gateway\GatewayInterface<User>
     */ 
    protected function getAssignees(): CollectionInterface
    {
        $request = $this->model->getRequest();
        /** @var User */
        $user = $request->getAttribute(User::class);

        $gw = $this->mm('SM:User');

        if (! $user->isSupervisor()) {
            $userGroups = $user->groups()->extract('id')->toList();
            if (! $userGroups) {
                # - Возвращаем пустую коллекцию, если пользователь не участвует ни в одной группе
                return Qore::collection([]);
            }
            $gw->with('groups')->where(function($_where) use ($userGroups, $user) {
                $_where(['@this.groups.id' => $userGroups])->or(['@this.id' => $user['id']]);
            });
        }

        return $gw->all();
    }

    /**
     * Get groups list
     *
     * @return \Qore\Collection\CollectionInterface<UserGroup>
     */
    protected function getGroups(): CollectionInterface
    {
        $request = $this->model->getRequest();
        /** @var User */
        $user = $request->getAttribute(User::class);

        return $user->isSupervisor()
            ? $this->mm('SM:UserGroup')->all()
            : $user->groups();
    }

    /**
     * Get groups list
     *
     * @return \Qore\Collection\CollectionInterface<Product>
     */
    protected function getProducts(): CollectionInterface
    {
        $products = $this->mm('SM:Product')->with('stages', fn($_gw) => $_gw->with('templates'))->all();
        $products->each(fn($_product) => $_product->structure())->compile();

        return $products;
    }

    /**
     * Apply filters
     *
     * @param \Qore\ORM\Gateway\GatewayInterface $_gw
     *
     * @return \Qore\ORM\Gateway\GatewayInterface
     */
    protected function applyFilters(GatewayInterface $_gw): GatewayInterface
    {
        $request = $this->model->getRequest();

        /** @var User */
        $user = $request->getAttribute(User::class);
        # - Подгружаем группы пользователя, в которых он участвует
        $this->mm($user)->with('groups')->one();
        # - Иницализируем связи
        $_gw->with('assignee')->with('status')
            ->with('group')->with('partner')
            ->with('followers')->with('productStages');

        # - Если агент не является супервайзером, то показываем только те заявки, 
        # - к которым он имеет отношение
        if (! $user->isSupervisor()) {
            $_gw->where(function($_where) use ($user) {
                $_where(function($_where) use ($user) {
                    $_where(['@this.assignee.id' => $user['id']])
                        ->or(['@this.group.id' => $user->groups()->extract('id')->toList()])
                        ->or(['@this.followers.id' => $user['id']]);
                });
            });
        }

        # - Применить фильтр по партнеру если есть
        $this->applyStatusFilter($_gw);
        # - Применить фильтр по исполнителю если есть
        $this->applyAssigneeFilter($_gw);
        # - Применить фильтр по группе если есть
        $this->applyGroupFilter($_gw);
        # - Применить фильтр по партнеру если есть
        $this->applyPartnerFilter($_gw);
        # - Применить фильтр по продукту если есть
        $this->applyProductStagesFilter($_gw);
        # - Применить фильтр по sla 
        $this->applySlaFilter($_gw);
        # - Применить фильтр по номеру заявки 
        $this->applyCodeFilter($_gw);
        # - Применить фильтр по дате создания 
        $this->applyCreatedFilter($_gw);
        # - Применить фильтр по дате обновления 
        $this->applyUpdatedFilter($_gw);
        # - Применить сортировку 
        $this->applySorting($_gw);

        return $_gw;
    }

    /**
     * Get manticore search engine
     *
     * @return \Manticoresearch\Search 
     */
    protected function search(): Search
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();
        /** @var User */
        $params = $request->getQueryParams();

        $query = $params['query'] ?? '';
        if (isset($params['number']) && $params['number']) {
            $query = preg_replace('/[^0-9A-Z]+/', '', $params['number']);
        }

        $sm = Qore::service(SynapseManager::class);
        /** @var Indexer */
        $indexer = $sm('Demand:Indexer')->plugin(Indexer::class);
        $search = $indexer->getEngine()->getIndex()->search($this->escape($query));

        /** @var User */
        $user = $request->getAttribute(User::class);
        # - Подгружаем группы пользователя, в которых он участвует
        $this->mm($user)->with('groups')->one();

        # - Если агент не является супервайзером, то показываем только те заявки, 
        # - к которым он имеет отношение
        if (! $user->isSupervisor()) {
            $filter = new BoolQuery();

            $filter->should(new Equals('assignee.id', (int)$user['id']));
            $filter->should(new Equals('any(followers)', (int)$user['id']));

            $groups = $user->groups()->extract('id')->map(fn ($_item) => (int)$_item)->toList();
            $groups && $filter->should(new In('group.id', $groups));

            $search->filter($filter);
        }

        # - Set cutoff option
        $search->option('cutoff', 0);
        # - Берем только родительские
        $search->filter('__idparent', 0);
        # - Применить фильтр по статусу если есть
        $this->applySearchStatusFilter($search);
        # - Применить фильтр по исполнителю если есть
        $this->applySearchAssigneeFilter($search);
        # - Применить фильтр по подписчикам если есть
        $this->applySearchFollowersFilter($search);
        # - Применить фильтр по группе если есть
        $this->applySearchGroupFilter($search);
        # - Применить фильтр по партнеру если есть
        $this->applySearchPartnerFilter($search);
        # - Применить фильтр по продукту если есть
        $this->applySearchProductStagesFilter($search);
        # - Применить фильтр по sla 
        $this->applySearchSlaFilter($search);
        # - Применить фильтр по номеру заявки 
        // $this->applySearchCodeFilter($search);
        # - Применить фильтр по дате создания 
        $this->applySearchCreatedFilter($search);
        # - Применить фильтр по дате обновления 
        $this->applySearchUpdatedFilter($search);
        # - Применить сортировку 
        $this->applySearchSorting($search);

        $search = clone $search;

        # - Set limit with offset
        $offset = $this->limit * ((int)($queryParams['page'] ?? 1) - 1);
        $search->limit($this->limit)
        ->offset($offset);

        if ($this->limit + $offset > 1000) {
            $search->option('max_matches', $this->limit + $offset);
        }

        return $search;
    }

    /**
     * Apply status filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void
     */
    protected function applySearchStatusFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['s'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['s'] as $statusId) {
            ($statusId = (int)$statusId) > 0
                ? array_push($positive, abs($statusId))
                : array_push($negative, abs($statusId));
        }

        $positive && $_search->filter('status.id', 'in', $positive);

        $negative && $_search->filter((new BoolQuery())
            ->should((new BoolQuery())->mustNot(new In('status.id', $negative)))
            ->should(new Equals('length(status)', 0)));
    }

    /**
     * Apply assignee filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchAssigneeFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['a'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['a'] as $assigneeId) {
            ($assigneeId = (int)$assigneeId) > 0
                ? array_push($positive, abs($assigneeId))
                : array_push($negative, abs($assigneeId));
        }

        $positive && $_search->filter('assignee.id', 'in', $positive);

        $negative && $_search->filter((new BoolQuery())
            ->should((new BoolQuery())->mustNot(new In('assignee.id', $negative)))
            ->should(new Equals('length(assignee)', 0)));
    }

    /**
     * Apply group filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchGroupFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['g'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['g'] as $groupId) {
            ($groupId = (int)$groupId) > 0
                ? array_push($positive, abs($groupId))
                : array_push($negative, abs($groupId));
        }

        $positive && $_search->filter('group.id', 'in', $positive);

        $negative && $_search->filter((new BoolQuery())
            ->should((new BoolQuery())->mustNot(new In('group.id', $negative)))
            ->should(new Equals('length(group)', 0)));
    }

    /**
     * Apply followers filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchFollowersFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['f'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['f'] as $groupId) {
            ($groupId = (int)$groupId) > 0
                ? array_push($positive, abs($groupId))
                : array_push($negative, abs($groupId));
        }

        $positive && $_search->filter('any(followers)', 'in', $positive);

        $negative && $_search->filter((new BoolQuery())
            ->should((new BoolQuery())->mustNot(new In('any(followers)', $negative)))
            ->should(new Equals('length(followers)', 0)));
    }

    /**
     * Apply partner filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchPartnerFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['p'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['p'] as $partnerId) {
            ($partnerId = (int)$partnerId) > 0
                ? array_push($positive, abs($partnerId))
                : array_push($negative, abs($partnerId));
        }

        $positive && $_search->filter('partner.id', 'in', $positive);

        $negative && $_search->filter((new BoolQuery())
            ->should((new BoolQuery())->mustNot(new In('partner.id', $negative)))
            ->should(new Equals('length(partner)', 0)));
    }

    /**
     * Apply product stages filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchProductStagesFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['r'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['r'] as $stageId) {
            ($stageId = (int)$stageId) > 0
                ? array_push($positive, abs($stageId))
                : array_push($negative, abs($stageId));
        }

        $positive && $_search->filter('any(productStages)', 'in', $positive);

        $negative && $_search->filter((new BoolQuery())
            ->should((new BoolQuery())->mustNot(new In('any(productStages)', $negative)))
            ->should(new Equals('length(productStages)', 0)));
    }

    /**
     * Apply sla filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchSlaFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $min = $params['slamin'] ?? null;
        $max = $params['slamax'] ?? null;

        if (is_null($min) && is_null($max)) {
            return;
        }

        (int)$min && $_search->filter('sla', '>=', (int)$min*60);
        (int)$max && $_search->filter('sla', '<=', (int)$max*60);
    }

    /**
     * Apply demand code filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchCodeFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $code = $params['number'] ?? null;

        if (is_null($code)) {
            return;
        }

        $code = preg_replace('/[^A-Z0-9]+/', '', mb_strtoupper($code));

        $code && $_search->filter('unique', '=', $code);
    }

    /**
     * Apply created filters
     *
     * @param \Manticoresearch\Search $_search
     *
     * @return void 
     */
    protected function applySearchCreatedFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $dateRange = $params['created'] ?? '';

        $dateFormat = '(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::00Z)?)';
        if (! preg_match(sprintf('/^%s$/u', sprintf('%s~%s', $dateFormat, $dateFormat)), $dateRange, $result)) {
            return;
        }

        $result = [
            (new DateTime($result[1]))->getTimestamp(),
            (new DateTime($result[2]))->getTimestamp(),
        ];

        $_search->filter('__created', 'range', $result);
    }

    /**
     * Apply updated filters
     *
     * @param \Manticoresearch\Search $_search 
     *
     * @return void 
     */
    protected function applySearchUpdatedFilter(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $dateRange = $params['updated'] ?? '';

        $dateFormat = '(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::00Z)?)';
        if (! preg_match(sprintf('/^%s$/u', sprintf('%s~%s', $dateFormat, $dateFormat)), $dateRange, $result)) {
            return;
        }

        $result = [
            (new DateTime($result[1]))->getTimestamp(),
            (new DateTime($result[2]))->getTimestamp(),
        ];

        $_search->filter('__updated', 'range', $result);
    }

    /**
     * Apply sorting
     *
     * @param \Manticoresearch\Search $_search
     *
     * @return void 
     */
    protected function applySearchSorting(Search $_search): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $sorting = explode('.', $params['sort'] ?? '');

        if (count($sorting) !== 2) {
            # - Сортировка по умолчанию
            $_search->sort('__updated', 'desc');
            return;
        }
        
        list($direction, $attribute) = $sorting;

        if (! in_array($direction, ['asc', 'desc']) || ! in_array($attribute, ['created', 'updated', 'sla'])) {
            return;
        }

        if (in_array($attribute, ['created', 'updated'])) {
            $attribute = '__' . $attribute;
        }

        $_search->sort($attribute, $direction);
    }

    /**
     * Apply status filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw
     *
     * @return void
     */
    protected function applyStatusFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['s'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['s'] as $statusId) {
            ($statusId = (int)$statusId) > 0
                ? array_push($positive, abs($statusId))
                : array_push($negative, abs($statusId));
        }

        $positive && $_gw->where(function($_where) use ($positive) {
            $_where->in('@this.status.id', $positive);
        });
        
        $negative && $_gw->where(function($_where) use ($negative) {
            $_where(fn ($_where) => $_where->notIn('@this.status.id', $negative)->or->isNull('@this.status.id'));
        });
    }

    /**
     * Apply assignee filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applyAssigneeFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['a'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['a'] as $assigneeId) {
            ($assigneeId = (int)$assigneeId) > 0
                ? array_push($positive, abs($assigneeId))
                : array_push($negative, abs($assigneeId));
        }

        $positive && $_gw->where(function($_where) use ($positive) {
            $_where->in('@this.assignee.id', $positive);
        });
        
        $negative && $_gw->where(function($_where) use ($negative) {
            $_where(fn ($_where) => $_where->notIn('@this.assignee.id', $negative)->or->isNull('@this.assignee.id'));
        });
    }

    /**
     * Apply group filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw
     *
     * @return void
     */
    protected function applyGroupFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['g'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['g'] as $groupId) {
            ($groupId = (int)$groupId) > 0
                ? array_push($positive, abs($groupId))
                : array_push($negative, abs($groupId));
        }

        $positive && $_gw->where(function($_where) use ($positive) {
            $_where->in('@this.group.id', $positive);
        });
        
        $negative && $_gw->where(function($_where) use ($negative) {
            $_where(fn ($_where) => $_where->notIn('@this.group.id', $negative)->or->isNull('@this.group.id'));
        });
    }

    /**
     * Apply subscriber filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw
     *
     * @return void
     */
    protected function applySubscriberFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['g'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['g'] as $groupId) {
            ($groupId = (int)$groupId) > 0
                ? array_push($positive, abs($groupId))
                : array_push($negative, abs($groupId));
        }

        $positive && $_gw->where(function($_where) use ($positive) {
            $_where->in('@this.subscriber.id', $positive);
        });
        
        $negative && $_gw->where(function($_where) use ($negative) {
            $_where(fn ($_where) => $_where->notIn('@this.group.id', $negative)->or->isNull('@this.group.id'));
        });
    }

    /**
     * Apply assignee filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applyPartnerFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['p'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['p'] as $partnerId) {
            ($partnerId = (int)$partnerId) > 0
                ? array_push($positive, abs($partnerId))
                : array_push($negative, abs($partnerId));
        }

        $positive && $_gw->where(function($_where) use ($positive) {
            $_where->in('@this.partner.id', $positive);
        });
        
        $negative && $_gw->where(function($_where) use ($negative) {
            $_where(fn ($_where) => $_where->notIn('@this.partner.id', $negative)->or->isNull('@this.partner.id'));
        });
    }

    /**
     * Apply product stages filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
    */
    protected function applyProductStagesFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();

        if (! isset($params['r'])) {
            return;
        }

        $positive = $negative = [];
        foreach ($params['r'] as $stageId) {
            ($stageId = (int)$stageId) > 0
                ? array_push($positive, abs($stageId))
                : array_push($negative, abs($stageId));
        }

        $positive && $_gw->where(function($_where) use ($positive) {
            $_where->in('@this.productStages.id', $positive);
        });
        
        $negative && $_gw->where(function($_where) use ($negative) {
            /** @var ModelManager */
            $mm = Qore::service(ModelManager::class);
            $select = $mm('SM:Demand')->select(fn($_select) => $_select->columns(['@this.id' => 'id'], true, false))->with('productStages')->where(['@this.productStages.id' => $negative])->buildSelect();

            $_where(fn ($_where) => $_where->notIn('@this.id', $select)->or->isNull('@this.productStages.id'));
        });
    }

    /**
     * Apply sla filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applySlaFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $min = $params['slamin'] ?? null;
        $max = $params['slamax'] ?? null;

        if (is_null($min) && is_null($max)) {
            return;
        }

        (int)$min && $_gw->where(function($_where) use ($min) {
            $_where->greaterThanOrEqualTo('@this.sla', (int)$min*60);
        });

        (int)$max && $_gw->where(function($_where) use ($max) {
            $_where->lessThanOrEqualTo('@this.sla', (int)$max*60);
        });
    }

    /**
     * Apply demand code filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applyCodeFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $code = $params['number'] ?? null;

        if (is_null($code)) {
            return;
        }

        $code = preg_replace('/[^A-Z0-9]+/', '', mb_strtoupper($code));
        $code && $_gw->where(function($_where) use ($code) {
            $_where(['@this.unique' => $code]);
        });
    }

    /**
     * Apply created filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applyCreatedFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $dateRange = $params['created'] ?? '';

        $dateFormat = '(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::00Z)?)';
        if (! preg_match(sprintf('/^%s$/u', sprintf('%s~%s', $dateFormat, $dateFormat)), $dateRange, $result)) {
            return;
        }

        $result = [
            (new DateTime($result[1]))->format('Y-m-d H:i'),
            (new DateTime($result[2]))->format('Y-m-d H:i'),
        ];

        $_gw->where(function($_where) use ($result) {
            $_where->between('@this.__created', ...$result);
        });
    }

    /**
     * Apply updated filters
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applyUpdatedFilter(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $dateRange = $params['updated'] ?? '';

        $dateFormat = '(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::00Z)?)';
        if (! preg_match(sprintf('/^%s$/u', sprintf('%s~%s', $dateFormat, $dateFormat)), $dateRange, $result)) {
            return;
        }

        $result = [
            (new DateTime($result[1]))->format('Y-m-d H:i'),
            (new DateTime($result[2]))->format('Y-m-d H:i'),
        ];

        $_gw->where(function($_where) use ($result) {
            $_where->between('@this.__updated', ...$result);
        });
    }

    /**
     * Apply sorting 
     *
     * @param \Qore\ORM\Gateway\Gateway $_gw 
     *
     * @return void 
     */
    protected function applySorting(Gateway $_gw): void 
    {
        $request = $this->model->getRequest();
        /** @var User */
        $params = $request->getQueryParams();
        
        $sorting = explode('.', $params['sort'] ?? '');

        
        if (count($sorting) !== 2) {
            # - Сортировка по умолчанию
            $_gw->select(function($_select) {
                $_select->order('@this.__updated desc');
            });

            return;
        }
        
        list($direction, $attribute) = $sorting;

        if (! in_array($direction, ['asc', 'desc']) || ! in_array($attribute, ['created', 'updated', 'sla'])) {
            return;
        }

        if (in_array($attribute, ['created', 'updated'])) {
            $attribute = '__' . $attribute;
        }

        $_gw->select(function($_select) use ($direction, $attribute) {
            $_select->order(sprintf('@this.%s %s', $attribute, $direction));
        });
    }

    /**
     * Prepare collection of partner emails
     *
     * @param \Qore\App\SynapseNodes\Components\Partner\Partner $_partner 
     * @param array $_emails 
     *
     * @return array 
     */
    private function getOutboxPartnerEmails(Partner $_partner, array $_emails): array
    {
        $create = $identifiers = [];
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        $validator = new EmailAddress();
        foreach ($_emails as $email) {
            if (is_numeric($email)) {
                $identifiers[] = $email;
            } elseif ($validator->isValid($email)) {
                $create[] = $mm('SM:OutboxPartnerEmail', [
                    'email' => $email,
                    'name' => null,
                ])->link('partner', $_partner);
            }
        }

        if ($create) {
            $mm(Qore::collection($create))->save();
        }

        $mm($_partner)->with('outboxEmails')->one();

        $identifiers = array_merge($identifiers, Qore::collection($create)->extract('id')->toList());

        $result = $_partner->outboxEmails()
            ->filter(fn($_email) => in_array($_email['id'], $identifiers))
            ->map(fn($_email) => ['name' => $_email['name'], 'email' => $_email['email']])
            ->toList();

        return $result;
    }

    /**
     * Escape for query search
     *
     * @param  $string 
     *
     * @return string
     */
    public function escape($string): string
    {
        $from = ['\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=', '<'];
        $to = ['\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=', '\<'];
        return str_replace($from, $to, $string);
    }

}
