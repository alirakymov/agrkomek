<?php

use Qore\NotifyManager\Adapter\AmqpAdapter;

return [
    'qore' => [
        'notify-manager' => [
            'adapter' => [
                'class' => AmqpAdapter::class,
                'options' => [
                    'host' => 'mps.rmq',
                    'port' => '5672',
                    'username' => 'qore_user',
                    'password' => 'qore_password',
                ]
            ],
            'stomp' => [
                'username' => 'qore_stomp',
                'password' => 'qore_stomp',
                'stomp-port' => 15674,
            ]
        ],
    ],
];
