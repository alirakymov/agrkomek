<?php

return [
    'cache' => [
        'adapter' => [
            'name' => 'redis',
            'options' => [
                'server' => [ 'host' => 'mps.redis' ]
            ],
        ]
    ]
];
