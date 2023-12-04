<?php

return [
    'metadata' => [
        'tables' => [
            'Moderator' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\Moderator\\Moderator',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-1' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'email',
                    ],
                    'attribute-2' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'password',
                    ],
                    'attribute-3' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'firstname',
                    ],
                    'attribute-4' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'lastname',
                    ],
                    'attribute-5' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'otp',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'Moderator_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'ModeratorRole' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\ModeratorRole\\ModeratorRole',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-6' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'title',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'ModeratorRole_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'ModeratorPermission' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\ModeratorPermission\\ModeratorPermission',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-7' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'component',
                    ],
                    'attribute-8' => [
                        'label' => 'Целое число',
                        'length' => 11,
                        'default' => '0',
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'alias' => 'level',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'ModeratorPermission_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'Article' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\Article\\Article',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-9' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'title',
                    ],
                    'attribute-10' => [
                        'label' => 'Целое число',
                        'length' => 11,
                        'default' => '0',
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'alias' => 'approved',
                        'null' => true,
                    ],
                    'attribute-11' => [
                        'label' => 'Большое текстовое значение',
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\LongText',
                        'alias' => 'content',
                    ],
                    'attribute-12' => [
                        'label' => 'Текстовое значение',
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'alias' => 'source',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'Article_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'ArticleType' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\ArticleType\\ArticleType',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-13' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'title',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'ArticleType_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'ImageStore' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\ImageStore\\ImageStore',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-14' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'name',
                    ],
                    'attribute-15' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'size',
                    ],
                    'attribute-16' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'uniqid',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'ImageStore_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'GuideCategory' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\GuideCategory\\GuideCategory',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-17' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'title',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'GuideCategory_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'Guide' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\Guide\\Guide',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-18' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'title',
                    ],
                    'attribute-19' => [
                        'label' => 'Большое текстовое значение',
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\LongText',
                        'alias' => 'content',
                    ],
                    'attribute-20' => [
                        'label' => 'Целое число',
                        'length' => 11,
                        'default' => '0',
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'alias' => 'approved',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'Guide_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'Consultancy' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\Consultancy\\Consultancy',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-21' => [
                        'label' => 'Текстовое значение',
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'alias' => 'question',
                    ],
                    'attribute-22' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'token',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'Consultancy_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'Machinery' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\Machinery\\Machinery',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-27' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'title',
                    ],
                    'attribute-28' => [
                        'label' => 'Целое число',
                        'length' => 11,
                        'default' => '0',
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'alias' => 'price',
                        'null' => true,
                    ],
                    'attribute-29' => [
                        'label' => 'Текстовое значение',
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'alias' => 'content',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'Machinery_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'Routes' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseBaseEntity',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'Routes_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'ConsultancySession' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\ConsultancySession\\ConsultancySession',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-23' => [
                        'label' => 'Символьное значение',
                        'length' => 255,
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Varchar',
                        'alias' => 'token',
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'ConsultancySession_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
            'ConsultancyMessage' => [
                'entity' => '\\Qore\\App\\SynapseNodes\\Components\\ConsultancyMessage\\ConsultancyMessage',
                'columns' => [
                    '__iSynapseService' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => true,
                        'default' => 0,
                    ],
                    '__idparent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__options' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'null' => true,
                        'default' => '',
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 1,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    'attribute-24' => [
                        'label' => 'Текстовое значение',
                        'null' => true,
                        'default' => null,
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Text',
                        'alias' => 'message',
                    ],
                    'attribute-25' => [
                        'label' => 'Целое число',
                        'length' => 11,
                        'default' => '0',
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'alias' => 'idConsultancy',
                        'null' => true,
                    ],
                    'attribute-26' => [
                        'label' => 'Целое число',
                        'length' => 11,
                        'default' => '0',
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'alias' => 'direction',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'parent' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__idparent',
                        ],
                    ],
                    'created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__created',
                        ],
                    ],
                    'updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__updated',
                        ],
                    ],
                    'indexed' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__indexed',
                        ],
                    ],
                    'deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            '__deleted',
                        ],
                    ],
                ],
            ],
            'ConsultancyMessage_References' => [
                'entity' => 'Qore\\SynapseManager\\Structure\\Entity\\SynapseReferenceBaseEntity',
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Integer',
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Column\\Timestamp',
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseEntityTo',
                        ],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\Index',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseEntityFrom',
                        ],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                        ],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => 'Qore\\ORM\\Mapper\\Table\\Constraint\\UniqueKey',
                        'columns' => [
                            'iSynapseRelation',
                            'iSynapseEntityTo',
                            'iSynapseServiceTo',
                            'iSynapseEntityFrom',
                            'iSynapseServiceFrom',
                        ],
                    ],
                ],
            ],
        ],
        'references' => [
            'ModeratorRole@role > Moderator@moderators' => [
                'type' => 3,
                'decorate-type' => '2',
                'via' => 'ModeratorRole_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'ModeratorRole_References.iSynapseRelation' => 3,
                ],
            ],
            'ModeratorRole@role > ModeratorPermission@permissions' => [
                'type' => 3,
                'decorate-type' => '2',
                'via' => 'ModeratorRole_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'ModeratorRole_References.iSynapseRelation' => 4,
                ],
            ],
            'ArticleType@type > Article@articles' => [
                'type' => 3,
                'decorate-type' => '2',
                'via' => 'ArticleType_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'ArticleType_References.iSynapseRelation' => 8,
                ],
            ],
            'GuideCategory@category > GuideCategory@guides' => [
                'type' => 3,
                'decorate-type' => '2',
                'via' => 'GuideCategory_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'GuideCategory_References.iSynapseRelation' => 11,
                ],
            ],
            'GuideCategory@category > Guide@guides' => [
                'type' => 3,
                'decorate-type' => '2',
                'via' => 'GuideCategory_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'GuideCategory_References.iSynapseRelation' => 12,
                ],
            ],
            'Routes@routes > Moderator@moderator' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 1,
                ],
            ],
            'Routes@routes > ModeratorRole@moderatorRole' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 2,
                ],
            ],
            'Routes@route > ModeratorPermission@moderatorpermissions' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 5,
                ],
            ],
            'Routes@route > Article@article' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 6,
                ],
            ],
            'Routes@route > ArticleType@articleType' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 7,
                ],
            ],
            'Routes@route > ImageStore@images' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 9,
                ],
            ],
            'Routes@route > GuideCategory@guideCategory' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 10,
                ],
            ],
            'Routes@route > Guide@guide' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 13,
                ],
            ],
            'Routes@route > Consultancy@consultancy' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 15,
                ],
            ],
            'Routes@route > Machinery@machinery' => [
                'type' => 3,
                'decorate-type' => '1',
                'via' => 'Routes_References(iSynapseEntityFrom,iSynapseEntityTo)',
                'conditions' => [
                    'Routes_References.iSynapseRelation' => 16,
                ],
            ],
        ],
    ],
];