<?php

return [
    'qore' => [
        'queue-manager' => [
            'jobs' => [
                '\\Qore\\App\\SynapseNodes\\Components\\Notification\\NotificationFirebase',
            ],
        ],
    ],
];