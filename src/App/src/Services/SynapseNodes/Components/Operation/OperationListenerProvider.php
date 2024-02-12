<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Laminas\EventManager\EventInterface;
use Qore\App\Services\Tracking\ListenerProviderInterface;
use Qore\App\Services\Tracking\TrackingInterface;
use Qore\App\SynapseNodes\Components\User\UserStack;
use Qore\Qore;
use Qore\QueueManager\QueueManager;

class OperationListenerProvider implements ListenerProviderInterface
{
    /**
     * @inheritdoc
     */
    public function subscribe(TrackingInterface $_tracking): void
    {
        $events = Qore::collection(Operation::getEvents());

        foreach ($events as $event) {
            # - Register listener for event 
            $_tracking->listen($event['id'], function(EventInterface $_event) {
                $qm = Qore::service(QueueManager::class);

                /** @var UserStack */
                $userStack = Qore::service(UserStack::class);

                $qm->publish(new OperationJob([
                    'target' => $_event->getTarget(),
                    'event-name' => $_event->getName(),
                    'params' => array_merge([
                        'initiator' => $userStack->current()
                    ], $_event->getParams()),
                ]));
            });
        }
    }

}
