<?php

return [
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager)',
        'name' => 'ArticleForm',
        'label' => 'Форма создания',
        'template' => '$title',
        'description' => 'Форма создания',
        'type' => 0,
        '__options' => [
            'fields-order' => [
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);language)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);title)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection);QSynapse:SynapseAttributes()',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);source)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);approved)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);content)',
            ],
        ],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager)',
        'name' => 'ArticleParserForm',
        'label' => 'Форма парсера',
        'template' => '$title',
        'description' => 'Форма парсера',
        'type' => 0,
        '__options' => [
            'fields-order' => [
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleParser);title)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleParser);link)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection);QSynapse:SynapseAttributes()',
            ],
        ],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager)',
        'name' => 'ArticleTypeForm',
        'label' => 'Создание',
        'template' => '$title',
        'description' => 'Создание',
        'type' => 0,
        '__options' => [
            'fields-order' => [
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleType);language)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleType);title)',
            ],
        ],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager)',
        'name' => 'Selection',
        'label' => 'Форма выбора',
        'template' => '$title',
        'description' => 'Форма выбора',
        'type' => 2,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ConsultancyCategory);Manager);ConsultancyCategoryForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ConsultancyCategory);Manager)',
        'name' => 'ConsultancyCategoryForm',
        'label' => 'Форма создания / редактирования',
        'template' => '$title',
        'description' => '',
        'type' => 0,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager)',
        'name' => 'GuideForm',
        'label' => 'GuideForm',
        'template' => '$title',
        'description' => '',
        'type' => 0,
        '__options' => [
            'fields-order' => [
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);title)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);SelectionField);QSynapse:SynapseAttributes()',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);approved)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);content)',
            ],
        ],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager)',
        'name' => 'GuideCategoryForm',
        'label' => 'GuideCategoryForm',
        'template' => '$title',
        'description' => 'GuideCategoryForm',
        'type' => 0,
        '__options' => [
            'fields-order' => [
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(GuideCategory);language)',
                'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(GuideCategory);title)',
            ],
        ],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);SelectionField)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager)',
        'name' => 'SelectionField',
        'label' => 'SelectionField',
        'template' => '$title',
        'description' => 'SelectionField',
        'type' => 2,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager)',
        'name' => 'MachineryForm',
        'label' => 'Форма создания / редактирования',
        'template' => '$title',
        'description' => 'Форма создания / редактирования',
        'type' => 0,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager);ModeratorForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager)',
        'name' => 'ModeratorForm',
        'label' => 'Форма редактирования модератора',
        'template' => '$firstname $lastname',
        'description' => 'Форма редактирования модератора',
        'type' => 0,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager)',
        'name' => 'ModeratorPermission',
        'label' => 'Создание ролей',
        'template' => '$component',
        'description' => 'Создание ролей',
        'type' => 0,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Connector);HiddenField)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Connector)',
        'name' => 'HiddenField',
        'label' => 'Скрытое поле',
        'template' => '$title',
        'description' => 'Скрытое поле',
        'type' => 1,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);HiddenField)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager)',
        'name' => 'HiddenField',
        'label' => 'Скрытое поле',
        'template' => '$title',
        'description' => 'Скрытое поле',
        'type' => 1,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);ModeratorRoleForm)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager)',
        'name' => 'ModeratorRoleForm',
        'label' => 'Форма создания',
        'template' => '$title',
        'description' => 'Форма создания',
        'type' => 0,
        '__options' => [],
    ],
    'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);Selection)' => [
        'iSynapseService' => 'QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager)',
        'name' => 'Selection',
        'label' => 'Форма выбора',
        'template' => '$title',
        'description' => 'Форма выбора',
        'type' => 2,
        '__options' => [],
    ],
];