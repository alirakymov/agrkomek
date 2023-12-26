<?php

return [
    'QSynapse:Synapses(Article)' => [
        'iParent' => null,
        'name' => 'Article',
        'description' => 'Новости',
        'tree' => 0,
    ],
    'QSynapse:Synapses(ArticleType)' => [
        'iParent' => 'Article',
        'name' => 'ArticleType',
        'description' => 'Тип новости',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Chat)' => [
        'iParent' => null,
        'name' => 'Chat',
        'description' => 'Чаты',
        'tree' => 0,
    ],
    'QSynapse:Synapses(ChatMessage)' => [
        'iParent' => 'Chat',
        'name' => 'ChatMessage',
        'description' => 'Сообщение',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Consultancy)' => [
        'iParent' => null,
        'name' => 'Consultancy',
        'description' => 'Консультации',
        'tree' => 0,
    ],
    'QSynapse:Synapses(ConsultancyMessage)' => [
        'iParent' => 'Consultancy',
        'name' => 'ConsultancyMessage',
        'description' => '',
        'tree' => 0,
    ],
    'QSynapse:Synapses(ConsultancySession)' => [
        'iParent' => 'Consultancy',
        'name' => 'ConsultancySession',
        'description' => 'Сессия консультации',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Guide)' => [
        'iParent' => null,
        'name' => 'Guide',
        'description' => 'Справочник',
        'tree' => 0,
    ],
    'QSynapse:Synapses(GuideCategory)' => [
        'iParent' => 'Guide',
        'name' => 'GuideCategory',
        'description' => '',
        'tree' => 1,
    ],
    'QSynapse:Synapses(ImageStore)' => [
        'iParent' => null,
        'name' => 'ImageStore',
        'description' => 'Изображения',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Machinery)' => [
        'iParent' => null,
        'name' => 'Machinery',
        'description' => 'Сельскохозяйственная техника ',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Moderator)' => [
        'iParent' => null,
        'name' => 'Moderator',
        'description' => 'Модератор',
        'tree' => 0,
    ],
    'QSynapse:Synapses(ModeratorPermission)' => [
        'iParent' => 'ModeratorRole',
        'name' => 'ModeratorPermission',
        'description' => 'Права роли модератора',
        'tree' => 0,
    ],
    'QSynapse:Synapses(ModeratorRole)' => [
        'iParent' => null,
        'name' => 'ModeratorRole',
        'description' => 'Роль модератора',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Routes)' => [
        'iParent' => null,
        'name' => 'Routes',
        'description' => 'Сервис роутов',
        'tree' => 0,
    ],
    'QSynapse:Synapses(User)' => [
        'iParent' => null,
        'name' => 'User',
        'description' => 'Пользователь',
        'tree' => 0,
    ],
    'QSynapse:Synapses(Weather)' => [
        'iParent' => null,
        'name' => 'Weather',
        'description' => 'Погода'."\r\n",
        'tree' => 0,
    ],
];