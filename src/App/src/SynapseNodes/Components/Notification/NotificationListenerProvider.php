<?php

namespace Qore\App\SynapseNodes\Components\Notification;

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

class NotificationListenerProvider implements ListenerProviderInterface
{
    /**
     * @inheritdoc
     */
    public function subscribe(TrackingInterface $_tracking): void
    {
        # - Demand change title event 
        $_tracking->listen(Notification::EVENT_MACHINERY_STATUS_UPDATE, function(EventInterface $_event) {
            $this->listenOnMachineryStatusUpdate($_event);
        });
    }

    /**
     * Machinery status update 
     *
     * @param \Laminas\EventManager\EventInterface $_event 
     *
     * @return void 
     */
    private function listenOnMachineryStatusUpdate(EventInterface $_event): void
    {
        /** @var Demand */
        $machinery = $_event->getTarget();
        $eventName = $_event->getName();

        // dump($machinery);
        // dump($eventName);
    }


}
