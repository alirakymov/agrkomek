<?php

use Qore\App\SynapseNodes\Components\Article\Manager\ArticleService;
use Qore\App\SynapseNodes\Components\ArticleType\Manager\ArticleTypeService; use Qore\App\SynapseNodes\Components\ConsultancyCategory\Manager\ConsultancyCategoryService;
use Qore\App\SynapseNodes\Components\Consultancy\Manager\ConsultancyService;
use Qore\App\SynapseNodes\Components\Guide\Manager\GuideService;
use Qore\App\SynapseNodes\Components\GuideCategory\Manager\GuideCategoryService;
use Qore\App\SynapseNodes\Components\Machinery\Manager\MachineryService;
use Qore\App\SynapseNodes\Components\ModeratorRole\Manager\ModeratorRoleService;
use Qore\App\SynapseNodes\Components\Moderator\Manager\ModeratorService;
use Qore\App\SynapseNodes\Components\User\Manager\UserService;

return [
    'app' => [
        'admin' => [
            'navigation-items' => [
                [
                    'label' => 'Модераторы',
                    'sublevel' => [
                        [
                            'label' => 'Список',
                            'privilege' => 1,
                            'icon' => 'fas fa-users',
                            'route' => [ModeratorService::class, 'index'],
                        ],
                        [
                            'label' => 'Роли',
                            'privilege' => 1,
                            'icon' => 'fas fa-users-cog',
                            'route' => [ModeratorRoleService::class, 'index'],
                        ],
                    ],
                ],
                [
                    'label' => 'Новости',
                    'sublevel' => [
                        [
                            'label' => 'Список',
                            'privilege' => 1,
                            'icon' => 'far fa-newspaper',
                            'route' => [ArticleService::class, 'index'],
                        ],
                        [
                            'label' => 'Типы',
                            'privilege' => 1,
                            'icon' => 'far fa-newspaper',
                            'route' => [ArticleTypeService::class, 'index'],
                        ],
                    ],
                ],
                [
                    'label' => 'Справочник',
                    'sublevel' => [
                        [
                            'label' => 'Категории',
                            'privilege' => 1,
                            'icon' => 'fas fa-book',
                            'route' => [GuideCategoryService::class, 'index'],
                        ],
                        [
                            'label' => 'Список',
                            'privilege' => 1,
                            'icon' => 'fas fa-book',
                            'route' => [GuideService::class, 'index'],
                        ],
                    ],
                ],
                [
                    'label' => 'Консультации',
                    'sublevel' => [
                        [
                            'label' => 'Обращения',
                            'privilege' => 1,
                            'icon' => 'fas fa-comments',
                            'route' => [ConsultancyService::class, 'index'],
                        ],
                        [
                            'label' => 'Категории',
                            'privilege' => 1,
                            'icon' => 'far fa-comment-dots',
                            'route' => [ConsultancyCategoryService::class, 'index'],
                        ],
                    ],
                ],
                [
                    'label' => 'Техника',
                    'sublevel' => [
                        [
                            'label' => 'Список',
                            'privilege' => 1,
                            'icon' => 'fas fa-tractor',
                            'route' => [MachineryService::class, 'index'],
                        ],
                    ],
                ],
                [
                    'label' => 'Пользователи',
                    'sublevel' => [
                        [
                            'label' => 'Список',
                            'privilege' => 1,
                            'icon' => 'fas fa-users',
                            'route' => [UserService::class, 'index'],
                        ],
                    ],
                ],
            ]
        ],
        'upload-paths' => [
            'global' => [
                'public' => [
                    'images' => [
                        'path' => PROJECT_PATH . '/../gstatic/public/uploads/images',
                        'uri' => '/global-images/{location}/{uniqid}'
                    ],
                    'files' => [
                        'path' => PROJECT_PATH . '/../gstatic/public/uploads/files',
                        'uri' => '/global-files/{location}/{uniqid}'
                    ],
                ],
            ],
            'local' => [
                'public' => [
                    'images' => [
                        'path' => PROJECT_PATH . '/public/uploads/images',
                        'uri' => '/uploads/images/{location}/{uniqid}'
                    ],
                    'files' => [
                        'path' => PROJECT_PATH . '/public/uploads/files',
                        'uri' => '/uploads/files/{location}/{uniqid}'
                    ],
                ],
                'private' => [
                    'images' => [
                        'path' => PROJECT_STORAGE_PATH . '/storage/images',
                        'uri' => '/private/storage/images/{location}/{uniqid}'
                    ],
                    'files' => [
                        'path' => PROJECT_STORAGE_PATH . '/storage/files',
                        'uri' => '/private/storage/files/{location}/{uniqid}'
                    ],
                ]
            ]
        ],
    ],
];
