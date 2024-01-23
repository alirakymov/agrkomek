<?php

return [
    'dependencies' => [
        'invokables' => [
            '\\Qore\\App\\Services\\Indexer\\ArticleIndexer',
        ],
    ],
    'console' => [
        'commands' => [
            '\\Qore\\App\\Services\\Indexer\\ArticleIndexer',
        ],
    ],
];