<?php

namespace Qore\App\SynapseNodes\Components\Demand;

use Laminas\EventManager\EventInterface;
use Qore\App\Services\Tracking\ListenerProviderInterface;
use Qore\App\Services\Tracking\TrackingInterface;
use Qore\App\SynapseNodes\Components\DemandEvent\DemandEvent;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\App\SynapseNodes\Components\User\UserStack;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\Sanitizer\SanitizerInterface;
use Qore\App\Services\Deferring\DeferringInterface;
use Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage;
use Qore\App\SynapseNodes\Components\Operation\Operation;
use Qore\App\SynapseNodes\Components\PartnerEmail\PartnerEmail;
use Qore\ORM\Sql\Where;
use Qore\SynapseManager\SynapseManager;
use Throwable;

class DemandListenerProvider implements ListenerProviderInterface
{
    /**
     * @inheritdoc
     */
    public function subscribe(TrackingInterface $_tracking): void
    {
        # - Demand change title event 
        $_tracking->listen(Demand::DEMAND_CHANGE_TITLE, function(EventInterface $_event) {
            // $this->listenOnDemandChangeTitle($_event);
        });

        # - Demand set assignee event
        $_tracking->listen(Demand::DEMAND_SET_ASSIGNEE, function (EventInterface $_event) {
            $this->listenOnDemandSetAssignee($_event);
        });

        # - Demand set group event
        $_tracking->listen(Demand::DEMAND_SET_GROUP, function (EventInterface $_event) {
            $this->listenOnDemandSetGroup($_event);
        });

        # - Demand set status event
        $_tracking->listen(Demand::DEMAND_SET_STATUS, function(EventInterface $_event) {
            $this->listenOnDemandSetStatus($_event);
        });
        
        # - Demand set partner event
        $_tracking->listen(Demand::DEMAND_SET_PARTNER, function(EventInterface $_event) {
            $this->listenOnDemandSetPartner($_event);
        });

        # - Demand set comment event
        $_tracking->listen(Demand::DEMAND_SET_COMMENT, function(EventInterface $_event) {
            $this->listenOnDemandSetCommment($_event);
        });

        # - Demand set message event
        $_tracking->listen(Demand::DEMAND_SET_MESSAGE, function (EventInterface $_event) {
            $this->listenOnDemandSetMessage($_event);
        });

        # - Demand set followers event
        $_tracking->listen(Demand::DEMAND_SET_FOLLOWERS, function (EventInterface $_event) {
            $this->listenOnDemandSetFollowers($_event);
        });

        # - Demand set followers event
        $_tracking->listen(Demand::DEMAND_UNSET_FOLLOWERS, function (EventInterface $_event) {
            $this->listenOnDemandUnsetFollowers($_event);
        });

        # - Demand sla changed event 
        $_tracking->listen(Demand::DEMAND_SLA_CHANGED, function (EventInterface $_event) {
            $this->listenOnDemandSlaChanged($_event);
        });

        # - Demand product stage set event 
        $_tracking->listen(Demand::DEMAND_SET_PRODUCT_STAGE, function (EventInterface $_event) {
            $this->listenOnDemandProductStageSet($_event);
        });
    }

    /**
     * Demand change title listener
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void 
     */
    private function listenOnDemandChangeTitle(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [],
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);
    }

    /**
     * Demand set status listener
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandSetStatus(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'previous' => $demand['current-status'] ?? null,
            ],
            'targetRelation' => 'status',
            'target' => $demand->status()->id,
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_STATUS, $demand, [
            'previous' => $demand['current-status']['id'] ?? null,
            'next' => $demand->status()->id,
        ]);
    }

    /**
     * Demand set comment listener
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandSetCommment(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        /** @var SanitizerInterface */
        $sanitizer = Qore::service(SanitizerInterface::class);

        # - Get event params
        $params = $_event->getParams();

        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'comment' => $sanitizer->sanitize($params['comment']),
            ],
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_COMMENT, $demand, [
            'comment' => $sanitizer->sanitize($params['comment']),
        ]);
    }

    /**
     * Demand set partner
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandSetPartner(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'previous' => $demand['current-partner'] ?? null,
            ],
            'targetRelation' => 'partner',
            'target' => $demand->partner()->id,
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_PARTNER, $demand, [
            'previous' => $demand['current-partner']['id'] ?? null,
            'next' => $demand->partner()->id,
        ]);
    }

    /**
     * Demand set assignee
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandSetAssignee(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'previous' => $demand['current-assignee'] ?? null,
            ],
            'targetRelation' => 'assignee',
            'target' => $demand->assignee()->id,
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_ASSIGNEE, $demand, [
            'previous' => $demand['current-assignee']['id'] ?? null,
            'next' => $demand->assignee()->id,
        ]);
    }

    /**
     * Demand set group
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandSetGroup(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'previous' => $demand['current-group'] ?? null,
            ],
            'targetRelation' => 'group',
            'target' => $demand->group()->id,
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_GROUP, $demand, [
            'previous' => $demand['current-group']['id'] ?? null,
            'next' => $demand->group()->id,
        ]);
    }

    /**
     * Demand set followers
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandSetFollowers(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'followers' => $demand->followers()->toList(),
            ],
            'targetRelation' => 'followers',
        ]);
        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_FOLLOWERS, $demand, [
            'followers' => $demand->followers()->toList(),
        ]);
    }

    /**
     * Demand unset followers
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */
    private function listenOnDemandUnsetFollowers(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'deleted-followers' => $demand['deleted-followers']->toList(),
            ],
            'targetRelation' => 'deleted-followers',
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_UNSET_FOLLOWERS, $demand, [
            'deleted-followers' => $demand['deleted-followers']->toList(),
        ]);
    }

    /**
     * Demand set message
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void
     */ 
    private function listenOnDemandSetMessage(EventInterface $_event): void
    {
        /** @var array */
        $params = $_event->getParams();

        /** @var DemandMessage */
        $message = $params['message'];
        if (! isset($message['isSend'])) {
            $this->registerProcessedDemandMessage($message, $_event);
        } else {
            $this->registerOutboxDemandMessage($message, $_event);
        }
    }

    /**
     * Listener on sla change event
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void 
     */
    private function listenOnDemandSlaChanged(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();

        /** @var DeferringInterface */
        $deferring = Qore::service(DeferringInterface::class);
        # - Считаем средний sla для всей заявки в отложенной задаче
        $deferring->defer(function(Demand $_demand) {
            /** @var TrackingInterface */
            $tracking = Qore::service(TrackingInterface::class);
            return $tracking(function() use ($_demand) {
                /** @var SynapseManager */
                $sm = Qore::service(SynapseManager::class);
                /** @var ModelManager */
                $mm = Qore::service(ModelManager::class);

                $demandEvents = $mm('SM:DemandEvent')->with('demand')->where(function($_where) use ($_demand) {
                    $_where->greaterThan('@this.slaDate', 0)->and(['@this.demand.id' => $_demand['id']]);
                })->all();

                $sla = [];
                $slaOpenedDate = null;

                foreach ($demandEvents as $demandEvent) {
                    if ((int)$demandEvent['sla'] > 0) {
                        array_push($sla, (int)$demandEvent['sla']);
                    } elseif ((int)$demandEvent['slaTarget'] == 0) {
                        $slaOpenedDate = is_null($slaOpenedDate) || $slaOpenedDate > (int)$demandEvent['slaDate'] 
                            ? (int)$demandEvent['slaDate']
                            : $slaOpenedDate;
                    }
                }

                $_demand['sla'] = array_sum($sla) / count($sla);
                $_demand['slaDate'] = (int)$slaOpenedDate;
                $mm($_demand)->save();

                return true;
            });

        }, [$demand]);
    }

    /**
     * Listener on product stage set
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void 
     */
    private function listenOnDemandProductStageSet(EventInterface $_event): void
    {
        /** @var Demand */
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        # - Get event params
        $params = $_event->getParams();

        $product = $params['next']['product-link']['id'] ?? null;
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'previous' => $params['previous']['id'] ?? null,
                'next' => $params['next']->id,
                'products' => ! is_null($product) ? [$product] : [],
            ],
            'targetRelation' => 'productStages',
            'target' => $params['next']->id,
        ]);

        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        $tracking->postpone(Operation::OPERATION_DEMAND_SET_PRODUCT_STAGE, $demand, [
            'previous' => $params['previous']['id'] ?? null,
            'next' => $params['next']->id,
        ]);
    }

    /**
     * Register processed demand message
     *
     * @param \Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage $_message 
     * @param \Laminas\EventManager\EventInterface $_event
     *
     * @return void
     */
    private function registerProcessedDemandMessage(DemandMessage $_message, EventInterface $_event): void
    {
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        /** @var SanitizerInterface */
        $sanitizer = Qore::service(SanitizerInterface::class);
        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'direction' => $_message['direction'],
                'from' => $_message['from'],
                'to' => $_message['to'],
                'subject' => $_message['subject'],
                'body' => $sanitizer->sanitize($_message->body),
                'messageId' => $_message['messageId'],
                'attachments' => $_message->attachments()
                    ->map(fn($_attachment) => $_attachment->toArray(true))
                    ->toList(),
            ],
            'targetRelation' => 'messages',
            'target' => $_message->id,
            'slaDate' => $_message['messageDate']->getTimestamp(),
            'slaTarget' => 0,
        ]);

        $demandEvent->link('demand', $demand);

        $this->saveDemandEvent($demandEvent);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        # - Fire demand message register event
        $tracking->postpone(Operation::OPERATION_DEMAND_MESSAGE_REGISTER, $demand, [
            'message' => $_message,
            'demand-event' => $demandEvent,
        ]);
        # - Fire sla changed event
        $demandEvent['slaDate'] > 0 && $tracking->fire(Demand::DEMAND_SLA_CHANGED, $demand);
    }

    /**
     * Register outbox demand message
     *
     * @param \Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage $_message 
     * @param \Laminas\EventManager\EventInterface $_event
     *
     * @return void
     */
    private function registerOutboxDemandMessage(DemandMessage $_message, EventInterface $_event): void
    {
        $demand = $_event->getTarget();
        $eventName = $_event->getName();
        
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        # - Load demand partner
        is_null($demand->partner()) && $mm($demand)->with('partner')->one();
        # - Break if parent is absent
        if (is_null($partner = $demand->partner())) {
            # - Send error
            return;
        }
        # - Load partner emails
        is_null($partner->emails()) && $mm($partner)->with('emails')->one();
        # - Break if partner doesn't have email
        if (is_null($email = $partner->emails()->first())) {
            # - Send error
            return;
        }

        $email['emailTemplate'] = $partner['emailTemplate'];

        /** @var UserStack */
        $userStack = Qore::service(UserStack::class);
        $user = $userStack->current();

        # Get sla targeted event
        $slaTarget = $mm('SM:DemandEvent')
            ->where([
                '@this.id' => $_message['slaTarget'],
                '@this.slaTarget' => 0,
            ])->one();

        # - Обнуляет целевое событие, если оно аннулировано
        $slaTarget = ! is_null($slaTarget) && (int)$slaTarget['slaDate'] == 0 ? null : $slaTarget;

        $currentTime =new \DateTime('now');
        $sla = ! is_null($slaTarget) ? $currentTime->getTimestamp() - (int)$slaTarget->slaDate : 0;

        $products = [];

        if (isset($_message['stages'])) {
            /** @var ModelManager */
            $mm = Qore::service(ModelManager::class);
            $stages = $mm('SM:ProductStage')->with('product')->all();

            foreach ($_message['stages'] as $istage) {
                if ($stage = $stages->firstMatch(['id' => $istage])) {
                    array_push($products, $stage->product()->id);
                }
            }
        }

        /** @var SanitizerInterface */
        $sanitizer = Qore::service(SanitizerInterface::class);
        /** @var DemandEvent */
        $demandEvent = $mm('SM:DemandEvent', [
            'event' => $eventName,
            'idemand' => $demand['id'],
            'data' => [
                'direction' => $_message['direction'],
                'from' => $_message['from'],
                'to' => $_message['to'],
                'subject' => $_message['subject'],
                'body' => $sanitizer->sanitize($_message->body ?? ''),
                'attachments' => $_message->attachments()->toList(),
                'sent' => null,
                'products' => $products,
                'replyMessageId' => $_message['replyMessageId'] ?? null,
            ],
            'targetRelation' => 'messages',
            'target' => $_message->id,
            'sla' => $sla,
            'slaDate' => $currentTime->getTimestamp(),
            'slaTarget' => ! is_null($slaTarget) ? $slaTarget->id : 0,
        ]);

        $attachments = $_message->attachments()->toList();
        $demandEvent->link('demand', $demand);
        $this->saveDemandEvent($demandEvent);

        # - Save sla target
        $slaChanged = false;
        if (! is_null($slaTarget)) {
            $slaTarget->slaTarget = $demandEvent->id;
            $slaTarget->sla = (-1) * $demandEvent->sla;
            $mm($slaTarget)->save();
            $slaChanged = true;
        }

        /** @var DeferringInterface */
        $deferring = Qore::service(DeferringInterface::class);
        # - Отправка письма клиенту
        $deferring->defer(function(DemandMessage $_message, PartnerEmail $_email, DemandEvent $_demandEvent, User $_user) {
            /** @var TrackingInterface */
            $tracking = Qore::service(TrackingInterface::class);
            return $tracking(function() use ($_message, $_email, $_demandEvent, $tracking, $_user) {
                try {
                    /** @var TrackingInterface */
                    $tracking = Qore::service(TrackingInterface::class);
                    list($result, $mail) = $_email->send($_message, $_user);

                    $tracking->postpone(
                        $result ? Operation::OPERATION_SEND_MAIL_SUCCESS : Operation::OPERATION_SEND_MAIL_FAILURE, 
                        $_message, 
                        [ 'email' => $_email, 'user' => $_user, 'demand-event' => $_demandEvent->toArray(true), 'result' => $mail ]
                    );

                    /** @var SynapseManager */
                    $sm = Qore::service(SynapseManager::class);
                    /** @var ModelManager */
                    $mm = Qore::service(ModelManager::class);

                    $_message['messageId'] = $result ? $mail->getLastMessageID() : null;
                    $_message['from'] = $_email->extract(['name', 'email']);
                    $_message['data'] = $mail;

                    $mm($_message)->save();

                    $_demandEvent['data'] = array_merge($_demandEvent['data'] ?? [], [
                        'from' => $_message['from'],
                        'sent' => $result,
                    ]);

                    $mm($_demandEvent)->save();
                    return true;

                } catch (Throwable $e) {
                    $tracking->fire(
                        Operation::OPERATION_SEND_MAIL_FAILURE, 
                        $_message, 
                        [ 'email' => $_email->toArray(true), 'user' => $_user, 'demand-event' => $_demandEvent->toArray(true), 'error' => $e->getMessage()]
                    );
                    dump($e);
                    return true;
                }
            });

        }, [$_message, $email, $demandEvent, $user]);

        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);
        # - Fire message send event
        $tracking->postpone(Operation::OPERATION_DEMAND_MESSAGE_SEND, $demand, [
            'message' => $_message,
        ]);
        # - Fire sla changed event
        $slaChanged && $tracking->fire(Demand::DEMAND_SLA_CHANGED, $demand);
    }

    /**
     * Save demand event
     *
     * @param \Qore\App\SynapseNodes\Components\DemandEvent\DemandEvent $_event 
     *
     * @return void
     */
    private function saveDemandEvent(DemandEvent $_event, DemandEvent $_optionalEvent = null): void
    {
        /** @var UserStack */
        $userStack = Qore::service(UserStack::class);
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        $_event->link('initiator', $userStack->current());
        $mm($_event)->save();
    }

}
