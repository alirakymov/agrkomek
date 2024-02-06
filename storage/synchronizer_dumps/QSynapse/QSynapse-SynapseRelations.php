<?php

return [
    'QSynapse:SynapseRelations(QSynapse:Synapses(Article);article;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'article',
        'iSynapseTo' => 'QSynapse:Synapses(Article)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Article);articles;QSynapse:Synapses(ArticleType);type)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(ArticleType)',
        'synapseAliasFrom' => 'articles',
        'iSynapseTo' => 'QSynapse:Synapses(Article)',
        'synapseAliasTo' => 'type',
        'type' => '2',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ArticleParser);articleParser;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'articleParser',
        'iSynapseTo' => 'QSynapse:Synapses(ArticleParser)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ArticleParser);sources;QSynapse:Synapses(ArticleType);type)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(ArticleType)',
        'synapseAliasFrom' => 'sources',
        'iSynapseTo' => 'QSynapse:Synapses(ArticleParser)',
        'synapseAliasTo' => 'type',
        'type' => '2',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ArticleType);articleType;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'articleType',
        'iSynapseTo' => 'QSynapse:Synapses(ArticleType)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Chat);chat;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'chat',
        'iSynapseTo' => 'QSynapse:Synapses(Chat)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Consultancy);consultancies;QSynapse:Synapses(ConsultancyCategory);category)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(ConsultancyCategory)',
        'synapseAliasFrom' => 'consultancies',
        'iSynapseTo' => 'QSynapse:Synapses(Consultancy)',
        'synapseAliasTo' => 'category',
        'type' => '2',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Consultancy);consultancies;QSynapse:Synapses(Moderator);moderator)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Moderator)',
        'synapseAliasFrom' => 'consultancies',
        'iSynapseTo' => 'QSynapse:Synapses(Consultancy)',
        'synapseAliasTo' => 'moderator',
        'type' => '2',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Consultancy);consultancy;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'consultancy',
        'iSynapseTo' => 'QSynapse:Synapses(Consultancy)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ConsultancyCategory);consultancyCategory;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'consultancyCategory',
        'iSynapseTo' => 'QSynapse:Synapses(ConsultancyCategory)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Guide);guide;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'guide',
        'iSynapseTo' => 'QSynapse:Synapses(Guide)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Guide);guides;QSynapse:Synapses(GuideCategory);category)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(GuideCategory)',
        'synapseAliasFrom' => 'guides',
        'iSynapseTo' => 'QSynapse:Synapses(Guide)',
        'synapseAliasTo' => 'category',
        'type' => '2',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(GuideCategory);guideCategory;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'guideCategory',
        'iSynapseTo' => 'QSynapse:Synapses(GuideCategory)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(GuideCategory);guides;QSynapse:Synapses(GuideCategory);category)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(GuideCategory)',
        'synapseAliasFrom' => 'guides',
        'iSynapseTo' => 'QSynapse:Synapses(GuideCategory)',
        'synapseAliasTo' => 'category',
        'type' => '2',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ImageStore);images;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'images',
        'iSynapseTo' => 'QSynapse:Synapses(ImageStore)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Machinery);machineries;QSynapse:Synapses(User);user)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(User)',
        'synapseAliasFrom' => 'machineries',
        'iSynapseTo' => 'QSynapse:Synapses(Machinery)',
        'synapseAliasTo' => 'user',
        'type' => '2',
        'description' => 'machineries',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Machinery);machinery;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'machinery',
        'iSynapseTo' => 'QSynapse:Synapses(Machinery)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Moderator);moderator;QSynapse:Synapses(Routes);routes)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'moderator',
        'iSynapseTo' => 'QSynapse:Synapses(Moderator)',
        'synapseAliasTo' => 'routes',
        'type' => '1',
        'description' => 'роуты модератора',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Moderator);moderators;QSynapse:Synapses(ModeratorRole);role)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(ModeratorRole)',
        'synapseAliasFrom' => 'moderators',
        'iSynapseTo' => 'QSynapse:Synapses(Moderator)',
        'synapseAliasTo' => 'role',
        'type' => '2',
        'description' => 'связь модератора с ролями',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ModeratorPermission);moderatorpermissions;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'moderatorpermissions',
        'iSynapseTo' => 'QSynapse:Synapses(ModeratorPermission)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ModeratorPermission);permissions;QSynapse:Synapses(ModeratorRole);role)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(ModeratorRole)',
        'synapseAliasFrom' => 'permissions',
        'iSynapseTo' => 'QSynapse:Synapses(ModeratorPermission)',
        'synapseAliasTo' => 'role',
        'type' => '2',
        'description' => 'Связь роли с доступами',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(ModeratorRole);moderatorRole;QSynapse:Synapses(Routes);routes)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'moderatorRole',
        'iSynapseTo' => 'QSynapse:Synapses(ModeratorRole)',
        'synapseAliasTo' => 'routes',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Search);search;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'search',
        'iSynapseTo' => 'QSynapse:Synapses(Search)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(User);user;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'user',
        'iSynapseTo' => 'QSynapse:Synapses(User)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => 'User',
    ],
    'QSynapse:SynapseRelations(QSynapse:Synapses(Weather);weather;QSynapse:Synapses(Routes);route)' => [
        'iSynapseFrom' => 'QSynapse:Synapses(Routes)',
        'synapseAliasFrom' => 'weather',
        'iSynapseTo' => 'QSynapse:Synapses(Weather)',
        'synapseAliasTo' => 'route',
        'type' => '1',
        'description' => '',
    ],
];