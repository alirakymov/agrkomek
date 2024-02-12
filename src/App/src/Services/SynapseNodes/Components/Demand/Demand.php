<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Demand;

use Qore\App\Services\Tracking\TrackingInterface;
use Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage;
use Qore\App\SynapseNodes\Components\DemandStatus\DemandStatus;
use Qore\App\SynapseNodes\Components\Operation\Operation;
use Qore\App\SynapseNodes\Components\Partner\Partner;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\App\SynapseNodes\Components\UserGroup\UserGroup;
use Qore\Collection\Collection;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\Sanitizer\SanitizerInterface;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Demand
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Demand extends SynapseBaseEntity
{
    /**
     * @var string - Событие на изменение темы заявки
     */
    const DEMAND_CHANGE_TITLE = 'demand.title.change';

    /**
     * @var string - Событие на изменение статуса заявки
     */
    const DEMAND_SET_STATUS = 'demand.status.set';

    /**
     * @var string - Событие на добавление нового комментария 
     */
    const DEMAND_SET_COMMENT = 'demand.comment.set';

    /**
     * @var string - Событие на отправление сообщения
     */
    const DEMAND_SET_MESSAGE = 'demand.message.set';

    /** 
     * @var string - Событие на назначение клиента 
     */
    const DEMAND_SET_PARTNER = 'demand.partner.set';

    /** 
     * @var string - Событие на назначение пользователя 
     */
    const DEMAND_SET_ASSIGNEE = 'demand.assignee.set';

    /** 
     * @var string - Событие на назначение группы 
     */
    const DEMAND_SET_GROUP = 'demand.group.set';

    /** 
     * @var string - Событие на назначение подписчика 
     */
    const DEMAND_SET_FOLLOWERS = 'demand.followers.set';

    /** 
     * @var string - Событие на удаление подписчика 
     */
    const DEMAND_UNSET_FOLLOWERS = 'demand.followers.unset';

    /** 
     * @var string - Событие на изменение метрики активности
     */
    const DEMAND_SLA_CHANGED = 'demand.sla.changed';

    /** 
     * @var string - Событие на привязку нового этапа продукта
     */
    const DEMAND_SET_PRODUCT_STAGE = 'demand.product.stage.set';

    /**
     * @var флаг для невелирования повторной инициализации события сохранения 
     */
    private bool $saveEventIsFired = false;

    /**
     * @var array - массив отслеживаемых атрибутов с соответствующим имеменм события трека
     */
    private array $trackedAttributes = [
        'title' => self::DEMAND_CHANGE_TITLE,
    ];

    /**
     * @var string[]
     */
    private array $trackingEvents = [];

    /**
     * @var bool - in transaction flag
     */
    private bool $inTransaction = false;

    /**
     * Merge with data and fire tracking events
     *
     * @param array $_data 
     *
     * @return Demand
     */
    public function merge(array $_data): Demand
    {
        foreach ($_data as $attr => $value) {
            if (!isset($this->trackedAttributes[$attr])) {
                continue;
            }

            $this->pushTrackingEvent($this->trackedAttributes[$attr]);
            $this[$attr] = $value;
        }

        return $this;
    }

    /**
     * Set status for current demand
     *
     * @param \Qore\App\SynapseNodes\Components\DemandStatus\DemandStatus $_status 
     *
     * @return Demand 
     */
    public function setStatus(DemandStatus $_status): Demand
    {
        if ($this->status() && (int)$this->status()->id == (int)$_status->id) {
            return $this;
        }

        # - Save current status
        if ($this->status()) {
            $this['current-status'] = $this->status()->toArray(true);
        }

        # - Link with new status
        $this->unlink('status', '*');
        $this->link('status', $_status);

        $this->pushTrackingEvent(static::DEMAND_SET_STATUS);

        return $this;
    }

    /**
     * Prepare demand for public
     *
     * @return array
     */
    public function prepare(): array
    {
        $return = $this->toArray(true);

        if (isset($return['assignee']['password'])) {
            unset(
                $return['assignee']['password'], 
                $return['assignee']['token'], 
                $return['assignee']['otp']
            );
        }

        if (isset($return['followers']) && $return['followers']) {
            foreach ($return['followers'] as &$follower) {
                unset(
                    $follower['password'], 
                    $follower['token'], 
                    $follower['otp']
                );
            }
        }

        return $return;
    }

    /**
     * Register new commment
     *
     * @param string $_comment 
     *
     * @return Demand 
     */
    public function setComment(string $_comment): Demand
    {
        $this->pushTrackingEvent(static::DEMAND_SET_COMMENT, [
            'comment' => $_comment,
        ]);
        return $this;
    }

    /**
     * Register new message
     *
     * @param \Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage $_message 
     *
     * @return Demand 
     */
    public function setMessage(DemandMessage $_message): Demand
    {
        $this->link('messages', $_message);

        $this->pushTrackingEvent(static::DEMAND_SET_MESSAGE, [
            'message' => $_message,
        ]);

        return $this;
    }

    /** 
     * Link partner with current demand
     *
     * @param \Qore\App\SynapseNodes\Components\Partner\Partner $_partner 
     *
     * @return Demand 
     */
    public function setPartner(Partner $_partner): Demand
    {
        # - Check if new partner and exists partners is identical
        if ($this->partner() && (int)$_partner['id'] === (int)$this->partner()->id) {
            return $this;
        }

        # - Save current partner
        if ($this->partner()) {
            $this['current-partner'] = $this->partner()->toArray(true);
        }

        # - link new parent
        $this->unlink('partner', '*');
        $this->link('partner', $_partner);

        $this->pushTrackingEvent(static::DEMAND_SET_PARTNER);
        return $this;
    }

    /** 
     * Link followers with current demand
     *
     * @param Collection<User> $_followers 
     *
     * @return Demand 
     */
    public function setFollowers(Collection $_followers): Demand
    {                
        # - link new follower
        $this->link('followers', $_followers);
        $this->pushTrackingEvent(static::DEMAND_SET_FOLLOWERS);
        return $this;
    }

    /** 
     * Unlink followers with current demand
     *
     * @param Collection<User> $_followers 
     *
     * @return Demand 
     */
    public function unsetFollowers(Collection $_followers): Demand
    {        
        foreach($_followers as $value) {
            $this->unlink('followers', $value);
        }
        
        $this['deleted-followers'] = $_followers;
        $this->pushTrackingEvent(static::DEMAND_UNSET_FOLLOWERS);

        return $this;
    }

    /**
     * Link assignee with current demand
     *
     * @param \Qore\App\SynapseNodes\Components\User\User $_assignee 
     *
     * @return Demand 
     */
    public function setAssignee(User $_assignee): Demand
    {
        # - Check if new assignee and exists assignee is identical
        if ($this->assignee() && (int)$_assignee['id'] === (int)$this->assignee()->id) {
            return $this;
        }

        # - Save current assignee
        if ($this->assignee()) {
            $this['current-assignee'] = $this->assignee()->toArray(true);
        }

        # - link new assignee
        $this->unlink('assignee', '*');
        $this->link('assignee', $_assignee);

        $this->pushTrackingEvent(static::DEMAND_SET_ASSIGNEE);
        return $this;
    }

    /**
     * Link group with current demand
     *
     * @param \Qore\App\SynapseNodes\Components\UserGroup\UserGroup $_group
     *
     * @return Demand 
     */
    public function setGroup(UserGroup $_group): Demand
    {
        # - Check if new group and exists group is identical
        if ($this->group() && (int)$_group['id'] === (int)$this->group()->id) {
            return $this;
        }

        # - Save current group 
        if ($this->group()) {
            $this['current-group'] = $this->group();
        }

        # - link new assignee
        $this->unlink('group', '*');
        $this->link('group', $_group);

        $this->pushTrackingEvent(static::DEMAND_SET_GROUP);
        return $this;
    }

    /**
     * Link product with stages
     *
     * @param int[] $_stages
     *
     * @return Demand
     */
    public function setProductStages(array $_stages): Demand
    {
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        $products = $mm('SM:Product')->with('stages')->all();

        $stages = [];
        $products = $products->each(function ($_product) use (&$stages) {
            $_product->stages()->each(fn ($_stage) => $_stage['product-link'] = $_product)->compile();
            $stages = array_merge($stages, $_product->stages()->toList());
        })->compile();

        $stages = Qore::collection($stages);

        foreach ($_stages as $key => $stage) {
            $newStage = $stages->firstMatch(['id' => $stage]);
            if (is_null($newStage)) {
                continue;
            }

            $newStage['product-link']->structure();

            $i = 0; 
            foreach ($newStage['product-link']->stages() as $stage) {
                $stage['priority'] = $i++;
            }

            foreach ($this->productStages() as $productStage) {
                $productStage = $stages->firstMatch(['id' => $productStage->id]);
                if ($productStage['product-link'] === $newStage['product-link']) {
                    if ($productStage['priority'] < $newStage['priority']) {
                        $this->unlink('productStages', $productStage);
                        break;
                    } else {
                        $newStage = null;
                        break;
                    }
                }
                # - Обнуляем для следующей итерации
                $productStage = null;
            }

            if (! is_null($newStage)) {
                $this->link('productStages', $newStage);
                $this->pushTrackingEvent(static::DEMAND_SET_PRODUCT_STAGE, [
                    'previous' => $productStage ?? null,
                    'next' => $newStage,
                ]);
            }

        }

        return $this;
    }

    /**
     * Register tracking event
     *
     * @param string $_event
     * @param array $_params (optional)
     *
     * @return Demand
     */
    public function pushTrackingEvent(string $_event, array $_params = []): Demand
    {
        $this->trackingEvents[] = [
            'event' => $_event,
            'params' => $_params,
        ];

        return $this;
    }

    /**
     * Get registered tracking events
     *
     * @return array
     */
    public function pullTrackingEvents(): array
    {
        $te = $this->trackingEvents;
        $this->trackingEvents = [];
        $this->inTransaction = false;
        return $te;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($_value): void
    {
        $this['title'] = $_value;
    }

    /**
     * Prepare unique for human readable format
     *
     * @return string 
     */
    public function formatUnique(): string
    {
        return sprintf(
            '%s-%s%s',
            mb_substr($this->unique, 0, 5),
            mb_substr($this->unique, 5, 4),
            mb_substr($this->unique, 9)
        );
    }

    /**
     * Set flag in transaction
     *
     * @param bool $_bool (optional)
     *
     * @return Demand|bool
     */
    public function inTransaction(bool $_bool = null)
    {
        if (is_null($_bool)) {
            return $this->inTransaction;
        }

        $this->inTransaction = $_bool;
        return $this;
    }

    /**
     * Fire tracking events of this demands
     *
     * @return void 
     */
    public function fireTrackingEvents(): void
    {
        $trackingEvents = $this->pullTrackingEvents();

        $tracking = Qore::service(TrackingInterface::class);

        foreach ($trackingEvents as $event) {
            $tracking->fire($event['event'], $this, $event['params']);
        }

        if (isset($this['flag-isnew']) && ! $this->saveEventIsFired) {
            $this->saveEventIsFired = true;
            unset($this['flag-isnew']);
            $tracking->fire(Operation::OPERATION_DEMAND_NEW, $this, [
                'demand-unique' => $this['unique'],
            ]);
        }
    }

    /**
     * subscribe
     *
     * @return void
     */
    public static function subscribe(): void
    {
        static::after('initialize', function ($_event) {
            $demand = $_event->getTarget();
            if (! isset($demand['unique'])) {
                $demand['unique'] = strtoupper(sprintf("%s%'.03d%s", date('y'), date('z'), bin2hex(random_bytes(4))));
            }
        });

        static::before('save', function ($_event) {
            $demand = $_event->getTarget();

            if ($demand->isNew()) {
                $demand['flag-isnew'] = true;
            }

            $demand['title'] = Qore::service(SanitizerInterface::class)->sanitize($demand['title']);
        });

        static::after('save', function ($_event) {
            $demand = $_event->getTarget();

            if (! $demand->inTransaction()) {
                $demand->fireTrackingEvents();
            }
        });

        parent::subscribe();
    }

}
