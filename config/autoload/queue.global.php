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
                    'username' => 'qore_user',
                    'password' => 'qore_password',
                ],
            ],
            'jobs' => [
                '\\Qore\\App\\Services\\SmsService\\SmsServiceJob',
            ],
        ],
    ],
];
