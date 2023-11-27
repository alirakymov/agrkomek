<?php

use Qore\QueueManager\Adapter\AmqpAdapter;

return [
    'qore' => [
        'queue-manager' => [
            'adapter' => [
                'class' => AmqpAdapter::class,
                'options' => [
                    'host' => 'mps.rmq',
                    'port' => '5672',
                    'username' => 'mps_user',
                    'password' => 'mps_password',
                ],
            ]
        ],
    ],
];
