<?php

use Qore\App\SynapseNodes\Components\Demand\DemandListenerProvider;
use Qore\App\SynapseNodes\Components\Notification\NotificationListenerProvider;
use Qore\App\SynapseNodes\Components\Operation\OperationListenerProvider;

return [
    'tracking' => [
        'providers' => [
            NotificationListenerProvider::class,       
        ]
    ]
];
