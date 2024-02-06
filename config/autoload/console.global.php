<?php

return [
    'dependencies' => [
        'invokables' => [
            '\\Qore\\Console\\Commands\\ProjectManager',
            '\\Qore\\Console\\Commands\\ClearCache',
            '\\Qore\\Console\\Commands\\OrmInpect',
            '\\Qore\\SynapseManager\\Command\\CodeBuilder',
            '\\Qore\\SynapseManager\\Command\\DataSynchronizer',
            '\\Qore\\SynapseManager\\Plugin\\Indexer\\IndexerCommand',
            '\\Qore\\SynapseManager\\Plugin\\Indexer\\IndexerProcess',
            '\\Qore\\App\\Services\\Indexer\\ArticleIndexer',
            '\\Qore\\App\\SynapseNodes\\Components\\ArticleParser\\ArticleFeedParser',
        ],
    ],
    'console' => [
        'commands' => [
            '\\Qore\\Console\\Commands\\ProjectManager',
            '\\Qore\\Console\\Commands\\ClearCache',
            '\\Qore\\Console\\Commands\\OrmInpect',
            '\\Qore\\SynapseManager\\Command\\CodeBuilder',
            '\\Qore\\SynapseManager\\Command\\DataSynchronizer',
            '\\Qore\\SynapseManager\\Plugin\\Indexer\\IndexerCommand',
            '\\Qore\\SynapseManager\\Plugin\\Indexer\\IndexerProcess',
            '\\Qore\\App\\Services\\Indexer\\ArticleIndexer',
            '\\Qore\\App\\SynapseNodes\\Components\\ArticleParser\\ArticleFeedParser',
        ],
    ],
];
