<?php

namespace Qore\App\SynapseNodes\Components\DemandEvent;

use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage;
use Qore\App\SynapseNodes\Components\DemandStatus\DemandStatus;
use Qore\App\SynapseNodes\Components\Partner\Partner;
use Qore\App\SynapseNodes\Components\ProductStage\ProductStage;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;

class DemandEventDecorator
{
    /**
     * @var \Qore\Collection\CollectionInterface - collection of DemandEvent's
     */
    private CollectionInterface $_events;

    /**
     * @var \Qore\App\SynapseNodes\Components\Demand\Demand - demand instance
     */
    private Demand $_demand;

    /**
     * @var CollectionInterface  - partners collection
     */
    private CollectionInterface $partnersCollection;

    /**
     * @var CollectionInterface  - assignees collection
     */
    private CollectionInterface $assigneesCollection;

    /**
     * @var CollectionInterface  - groups collection
     */
    private CollectionInterface $groupsCollection;

    /**
     * @var CollectionInterface<DemandStatus>|null - status list
     */
    private static ?CollectionInterface $statusList = null;

    /**
     * @var CollectionInterface<ProductStage> - product statges collection
     */
    private ?CollectionInterface $productStagesCollection = null;

    /**
     * Constructor
     *
     * @param \Qore\Collection\CollectionInterface $_events
     * @param \Qore\App\SynapseNodes\Components\Demand\Demand $_demand
     */
    public function __construct(CollectionInterface $_events, Demand $_demand)
    {
        $this->_events = $_events;
        $this->_demand = $_demand;
    }

    /**
     * @inheritdoc
     */
    public function decorate(): CollectionInterface
    {
        $this->preloadData();

        return Qore::collection($this->_events->each(function(DemandEvent $_demandEvent) {
            $this->decorateEvent($_demandEvent);
        })->compile());
    }

    /**
     * Decorate event instance
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateEvent(DemandEvent $_demandEvent): void
    {
        switch(true) {
            case $_demandEvent->event === Demand::DEMAND_CHANGE_TITLE:
                $this->decorateChangeTitle($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_STATUS:
                $this->decorateSetStatus($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_COMMENT:
                $this->decorateSetComment($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_MESSAGE:
                $this->decorateSetMessage($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_PARTNER:
                $this->decorateSetPartner($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_ASSIGNEE:
                $this->decorateSetAssignee($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_GROUP:
                $this->decorateSetGroup($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_FOLLOWERS:
                $this->decorateSetFollowers($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_UNSET_FOLLOWERS:
                $this->decorateUnsetFollowers($_demandEvent);
                break;
            case $_demandEvent->event === Demand::DEMAND_SET_PRODUCT_STAGE:
                $this->decorateSetProductStage($_demandEvent);
                break;
        }
    }

    /**
     * Decorate for change title event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void
     */
    private function decorateChangeTitle(DemandEvent $_demandEvent): void
    {
        $_demandEvent['description'] = sprintf('Изменена тема в заявке %s', $this->_demand->formatUnique());
        $_demandEvent['icon'] = [
            'icon' => 'fas fa-pencil-alt',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-gray-light',
        ];
    }

    /**
     * Decorate for set status event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateSetStatus(DemandEvent $_demandEvent): void
    {
        $status = self::getStatusById((int)$_demandEvent['target']) ?? [
            'title' => 'Неопределенный',
        ];

        $_demandEvent['description'] = sprintf('Заявка переведена в статус <span class="badge bg-danger-light text-xmodern">%s</span>', $status['title']);
        $_demandEvent['icon'] = [
            'icon' => 'flag',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-danger-light',
        ];
    }

    /**
     * Decorate for set commebt event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateSetComment(DemandEvent $_demandEvent): void
    {
        $_demandEvent['description'] = $_demandEvent['data']['comment'] ?? 'Неизвестный комментарий';
        $_demandEvent['icon'] = [
            'icon' => 'comment',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-info-light',
        ];
    }

    /**
     * Decorate set message
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void
     */
    private function decorateSetMessage(DemandEvent $_demandEvent): void
    {
        $data = $_demandEvent['data'];
        if ($data['direction'] == DemandMessage::INBOX) {
            $mm = Qore::service(ModelManager::class);
            $email = $mm('SM:OutboxPartnerEmail')
                ->where(['@this.email' => $data['from']['email']])
                ->one();
            $data['from'] = $email->toArray(true);
            $description = 'Входящее письмо от <span class="badge bg-gray-light text-xmodern me-2">%s, %s</span><br>%s';
            $params = [$data['from']['email'], $data['from']['name'], $data['subject']];
            $_demandEvent['can-reply'] = $data['messageId'] ?? false;
            $_demandEvent['replyId'] = $data['from']['id'];
        } else {
            $description = 'Исходящее письмо на %s <br>%s';
            $params = [
                Qore::collection($data['to'] ?: [])
                    ->map(fn($_address) => sprintf(
                        '<span class="badge bg-gray-light text-xmodern me-2">%s, %s</span>', 
                        $_address['name'], $_address['email']
                    ))->reduce(fn($_acc, $_address) => sprintf('%s %s', $_acc, $_address)),
                 $data['subject']
            ];
            $_demandEvent['can-resend'] = true;
        }

        $_demandEvent['description'] = vsprintf($description, $params);
        $_demandEvent['folded-text'] = $data['body'];
        $_demandEvent['icon'] = [
            'icon' => $data['direction'] == DemandMessage::INBOX ? 'mail' : 'outgoing_mail',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => $data['direction'] == DemandMessage::INBOX ? 'bg-success-light' : 'bg-xdream-lighter',
        ];
    }

    /**
     * Decorate for set partner event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateSetPartner(DemandEvent $_demandEvent): void
    {
        /** @var Partner|null */
        $previousPartner = isset($_demandEvent['data']['previous']) 
            ? $this->partnersCollection->firstMatch(['id' => $_demandEvent['data']['previous']]) 
            : null;
        /** @var Partner */
        $currentPartner = $this->partnersCollection->firstMatch(['id' => $_demandEvent['target']]);

        $undefined = [ 'name' => 'Неопределенный', ];

        $previousPartner ??= $undefined;
        $currentPartner ??= $undefined;

        $_demandEvent['description'] = sprintf('Заявка закреплена за клиентом <span class="badge bg-gray-light text-xmodern">%s</span>', $currentPartner['name']);
        $_demandEvent['icon'] = [
            'icon' => 'emoji_transportation',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-gray-light',
        ];
    }

    /**
     * Decorate for set assignee event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateSetAssignee(DemandEvent $_demandEvent): void
    {
        /** @var User|null */
        $previousAssignee= isset($_demandEvent['data']['previous']) 
            ? $this->assigneesCollection->firstMatch(['id' => $_demandEvent['data']['previous']['id']]) 
            : null;
        /** @var User */
        $currentAssignee = $this->assigneesCollection->firstMatch(['id' => $_demandEvent['target']]);
        $undefined = [ 'fullname' => 'Неопределенный', ];

        $previousAssignee ??= $undefined;
        $currentAssignee ??= $undefined;
        
        $_demandEvent['description'] = sprintf('Исполнителем по заявке назначен(a) <span class="badge bg-warning-light text-xmodern">%s</span>', $currentAssignee['fullname']);
        $_demandEvent['icon'] = [
            'icon' => 'account_circle',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-warning-light',
        ];
    }

    /**
     * Decorate for set assignee event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateSetGroup(DemandEvent $_demandEvent): void
    {
        /** @var User|null */
        $previousGroup= isset($_demandEvent['data']['previous']) 
            ? $this->groupsCollection->firstMatch(['id' => $_demandEvent['data']['previous']['id']]) 
            : null;
        /** @var User */
        $currentGroup = $this->groupsCollection->firstMatch(['id' => $_demandEvent['target']]);
        $undefined = [ 'title' => 'title', ];

        $previousGroup ??= $undefined;
        $currentGroup ??= $undefined;
        
        $_demandEvent['description'] = sprintf('Заявка закреплена за группой <span class="badge bg-gray-light text-xmodern">%s</span>', $currentGroup['title']);
        $_demandEvent['icon'] = [
            'icon' => 'group',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-gray-light',
        ];
    }

    /**
     * Decorate for set followers event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateSetFollowers(DemandEvent $_demandEvent): void
    {
        $allFollowersName = Qore::collection($_demandEvent['data']['followers'])
            ->reduce(fn($_acc, $_follower) => $_acc . sprintf('<span class="badge bg-info-light text-xmodern me-2">%s</span>', $_follower->fullname),"");

        $_demandEvent['description'] = sprintf('Подписанные на заявку %s', $allFollowersName);
        $_demandEvent['icon'] = [
            'icon' => 'groups',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-info-light',
        ];
    }

    /**
     * Decorate for unset followers event
     *
     * @param DemandEvent $_demandEvent 
     *
     * @return void 
     */
    private function decorateUnsetFollowers(DemandEvent $_demandEvent): void
    {
        $allFollowersName = Qore::collection($_demandEvent['data']['deleted-followers'])
                    ->reduce(fn($_acc, $_follower) => $_acc . sprintf('<span class="badge bg-danger-light text-xmodern me-2">%s</span>', $_follower->fullname),"");

        $_demandEvent['description'] = sprintf('Удалены с заявки %s', $allFollowersName);
        $_demandEvent['icon'] = [
            'icon' => 'groups',
            'icon-color' => 'text-xmodern',
            'icon-bgcolor' => 'bg-danger-light',
        ];
    }

    /**
     * Decorate set product stage
     *
     * @param DemandEvent $_demandEvent 
     * @return void 
     */
    private function decorateSetProductStage(DemandEvent $_demandEvent): void
    {
        $previousStage = ! is_null($_demandEvent['data']['previous'])
            ? $this->productStagesCollection->firstMatch(['id' => $_demandEvent['data']['previous']])
            : null;

        $nextStage = ! is_null($_demandEvent['data']['next'])
            ? $this->productStagesCollection->firstMatch(['id' => $_demandEvent['data']['next']])
            : null;

        $text = is_null($previousStage) 
            ? sprintf(
                'К заявке добавлена услуга <span class="badge bg-xinspire-lighter text-xinspire-dark me-2">%s</span> 
                с этапом <span class="badge bg-gray-light text-xmodern me-2">%s</span>', 
                $nextStage->product()->title,
                $nextStage->title
            ) : sprintf(
                'Услуга <span class="badge bg-xinspire-lighter text-xinspire-dark me-2">%s</span> 
                переведена с этапа <span class="badge bg-gray-light text-xmodern me-2">%s</span>
                на этап <span class="badge bg-gray-light text-xmodern me-2">%s</span>', 
                $nextStage->product()->title,
                $previousStage->title,
                $nextStage->title,
            );

        $_demandEvent['description'] = $text;
        $_demandEvent['icon'] = [
            'icon' => $nextStage->product()->icon,
            'icon-color' => 'text-xinspire-dark',
            'icon-bgcolor' => 'bg-xinspire-lighter',
        ];
    }

    /**
     * Preload data for decorate demand events
     *
     * @return void
     */
    private function preloadData(): void
    {
        # - Preload partners from all demand events
        $this->preloadPartners();
        # - Preload assignees from all demand events
        $this->preloadAssignee();
        # - Preload groups from all demand events
        $this->preloadGroups();
        # - Preload product stages
        $this->preloadProductStages();
    }

    /**
     * Preload partners from all events
     *
     * @return void 
     */
    private function preloadPartners(): void
    {
        # - Collect partners identifiers
        $partners = [];
        foreach ($this->_events as $event) {
            if ($event->event === Demand::DEMAND_SET_PARTNER) {
                if (isset($event['data']['previous'])) {
                    $partners[] = $event['data']['previous']['id'];
                }
                $partners[] = $event->target;
            }
        }

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        # - Load partners from database
        $this->partnersCollection = $mm('SM:Partner')->where(['@this.id' => $partners])->all();
    }

    /**
     * Preload assignee from all events
     *
     * @return void 
     */
    private function preloadAssignee(): void
    {
        # - Collect partners identifiers
        $assignee = [];
        foreach ($this->_events as $event) {
            if ($event->event === Demand::DEMAND_SET_ASSIGNEE) {
                if (isset($event['data']['previous'])) {
                    $assignee[] = $event['data']['previous']['id'];
                }
                $assignee[] = $event->target;
            }
        }

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        # - Load assignees from database
        $this->assigneesCollection = $mm('SM:User')->where(['@this.id' => $assignee])->all();
    }

    /**
     * Preload group from all events
     *
     * @return void 
     */
    private function preloadGroups(): void
    {
        # - Collect partners identifiers
        $groups = [];
        foreach ($this->_events as $event) {
            if ($event->event === Demand::DEMAND_SET_GROUP) {
                if (isset($event['data']['previous'])) {
                    $groups[] = $event['data']['previous']['id'];
                }
                $groups[] = $event->target;
            }
        }

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        # - Load groups from database
        $this->groupsCollection = $mm('SM:UserGroup')->where(['@this.id' => $groups])->all();
    }

    /**
     * Preload product stages from all events
     *
     * @return void 
     */
    private function preloadProductStages(): void
    {
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        # - Load groups from database
        $this->productStagesCollection = $mm('SM:ProductStage')->with('product')->all();
    }

    /**
     * Find status by identifer
     *
     * @param int $_id
     *
     * @return DemandStatus|null
     */
    public static function getStatusById(int $_id): ?DemandStatus
    {
        $mm = Qore::service(ModelManager::class);
        if (is_null(self::$statusList)) {
            self::$statusList = $mm('SM:DemandStatus')->all();
        }

        return self::$statusList->filter(fn($_status) => (int)$_status['id'] === $_id)->first();
    }

}
