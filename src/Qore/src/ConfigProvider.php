<?php

declare(strict_types=1);

namespace Qore;

use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\HttpUserAgent;
use Qore\Form\FormManagerInterface;
use Twig;
use Mezzio\MiddlewareContainer;
use Mezzio\Twig\TwigExtension;
use Mezzio\Router\FastRouteRouter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Qore\Csrf\CsrfInterface;
use Qore\Diactoros\UriFactory;
use Qore\Form\FormManager;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return array_merge(
            [
                'dependencies' => $this->getDependencies(),
                'orm' => $this->getORMOptions(),
                'qore' => $this->getQoreOptions(),
                'app' => $this->getAppConfigs(),
                'twig' => $this->getTwigOptions(),
                'router' => $this->getRouterConfigs(),
                'templates' => $this->getTemplates(),
            ]
            , $this->getSessionsConfig()
        );
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
                Debug\ExecutionMeasureMiddleware::class,
                UriFactoryInterface::class => UriFactory::class,
            ],
            'aliases' => [
                'app' => Application::class,
                'console' => ConsoleApplication::class,
                'debug' => Debug\DebugBar::class,
                'sesison' => SessionManager\SessionManager::class,
                'mm' => ORM\ModelManager::class,
                FormManager::class => FormManagerInterface::class,
            ],
            'abstract_factories' => [
            ],
            'factories' => [
                Qore::class => QoreFactory::class,
                QoreConsole::class => QoreConsoleFactory::class,
                MiddlewareContainer::class => Middleware\MiddlewareContainerFactory::class,
                FastRouteRouter::class => Router\FastRouteRouterFactory::class,
                Router\RouterInterface::class => Router\FastRouteRouter::class,
                Router\RouteCollector::class => Router\RouteCollectorFactory::class,
                Database\Adapter\Adapter::class => Database\Adapter\AdapterFactory::class,
                Auth\AuthAdapter::class => Auth\AuthAdapterFactory::class,
                Auth\AuthenticationService::class => Auth\AuthenticationServiceFactory::class,
                ORM\ModelManager::class => ORM\ModelManagerFactory::class,
                ORM\Entity\Provider::class => ORM\Entity\ProviderFactory::class,
                ORM\Gateway\Provider::class => ORM\Gateway\ProviderFactory::class,
                ORM\Mapper\Provider::class => ORM\Mapper\ProviderFactory::class,
                DealingManager\DealingManager::class => DealingManager\DealingManagerFactory::class,
                EventManager\EventManager::class => EventManager\EventManagerFactory::class,
                CacheManager\CacheManager::class => CacheManager\CacheManagerFactory::class,
                Form\FormManagerInterface::class => Form\FormManagerFactory::class,
                Form\Protector\TightnessProtector::class => Form\Protector\TightnessProtectorFactory::class,
                Form\Decorator\QoreFront::class => Form\Decorator\QoreFrontFactory::class,
                Flash\FlashMessageMiddleware::class => Flash\FlashMessagesFactory::class,
                ServerRequestInterface::class => Diactoros\ServerRequestFactory::class,
                Twig\Environment::class => Template\Twig\TwigEnvironmentFactory::class,
                TwigExtension::class    => Template\Twig\TwigExtensionFactory::class,
                UploadManager\UploadManager::class => UploadManager\UploadManagerFactory::class,
                ImageManager\ImageManager::class => ImageManager\ImageManagerFactory::class,
                SessionManager\SessionManager::class => SessionManager\SessionManagerFactory::class,
                SessionManager\SessionMiddleware::class => SessionManager\SessionMiddlewareFactory::class,
                Helper\Helper::class => Helper\HelperFactory::class,
                Debug\DebugBar::class => Debug\DebugBarFactory::class,
                Daemon\Supervisor\SupervisorConfigurator::class => Daemon\Supervisor\SupervisorConfiguratorFactory::class,
                Daemon\Supervisor\Supervisor::class => Daemon\Supervisor\SupervisorFactory::class,
            ],
            'shared' => [
                Form\FormManager::class => false,
                Form\Decorator\QoreFront::class => false,
                UploadManager\UploadedFile::class => false,
                ImageManager\ImageManager::class => false,
                DealingManager\DealingManager::class => false,
            ],
            'initializers' => [
                ServiceManager\Initializer::class
            ],
            'initializer-targets' => [
                Middleware\BaseMiddleware::class => [
                    ServiceManager\Initializers\MiddlewaresInitializer::class,
                ],
            ],
        ];
    }

    /**
     * getQoreOptions
     *
     * @return array
     */
    public function getQoreOptions() : array
    {
        return [
            'upload-dir' => PROJECT_STORAGE_PATH . DS . 'uploads',
            'paths' => [
                'services-files' => touch_dir([PROJECT_PATH, 'storage', 'services']),
                'logs-files' => touch_dir([PROJECT_PATH, 'storage', 'logs']),
                'debuglog-files' => touch_dir([PROJECT_PATH, 'storage', 'logs', 'debug']),
                'qore-fe' => realpath(QORE_FRONT_PATH),
                'project-path' => realpath(PROJECT_PATH),
                'frontapp' => realpath(PROJECT_FRONTAPP_PATH),
                'cache-dir' => touch_dir([PROJECT_STORAGE_PATH, 'cache']),
            ],
            'daemons' => [
                'supervisor' => [
                    'uri' => 'http://127.0.0.1:9001/RPC2'
                ]
            ],
            'data-synchronizer' => [
                'dump-dir' => touch_dir([PROJECT_PATH, 'storage', 'synchronizer_dumps']),
                'targets' => [
                    'QSynapse' => [
                        'Synapses(name)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                            'specific-mapping' => [
                                'iParent' => 'QSynapse:Synapses',
                            ],
                        ],
                        'SynapseAttributes(iSynapse, name)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                        ],
                        'SynapseRelations(iSynapseTo, synapseAliasFrom, iSynapseFrom, synapseAliasTo)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                        ],
                        'SynapseServices(iSynapse, name)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                        ],
                        'SynapseServiceSubjects(iSynapseRelation, iSynapseServiceFrom, iSynapseServiceTo)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                        ],
                        'SynapseServiceForms(iSynapseService, name)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                            'specific-mapping' => [
                                '__options.fields-order' => 'QSynapse:SynapseServiceFormFields',
                            ],
                        ],
                        'SynapseServiceFormFields(iSynapseServiceForm, iSynapseServiceSubjectForm, iSynapseAttribute)' => [
                            'exclude' => ['__idinsert', '__version', '__keep', '__processedEvents'],
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * getQoreOptions
     *
     * @return array
     */
    public function getORMOptions() : array
    {
        return [
            'QSystem' => [
                /* {{{ */
                'metadata' => [
                    'tables' => [
                        'Users' => [
                            'entity' => Core\Entities\QSystemUser::class,
                            'columns' => [
                                'email'      => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'password'   => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'firstName'  => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'lastName'   => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'middleName' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => true,
                                ],
                                'privilege' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 3,
                                    'null' => true,
                                ],
                                'tsCreated' => [
                                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                                    'null' => false,
                                    'default' => null,
                                ],
                                'tsUpdated' => [
                                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                                    'null' => false,
                                ],
                            ],
                            'constraints' => [
                                'email-password' => [
                                    'type' => ORM\Mapper\Table\Constraint\UniqueKey::class,
                                    'columns' => ['email'],
                                ]
                            ]
                        ],
                        'Groups' => [
                            'columns' => [
                                'name' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ]
                            ]
                        ],
                        'UsersGroups' => [
                            'columns' => [
                                'iUser' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => false,
                                ],
                                'iGroup' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => false,
                                ],
                                'active' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                ],
                            ]
                        ],
                        'Services' => [
                            'entity' => Core\Entities\QSystemService::class,
                            'columns' => [
                                'name' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'command' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                                'autostart' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                    'default' => 0
                                ],
                                'autorestart' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                    'default' => 0
                                ],
                                'numprocs' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 3,
                                    'null' => false,
                                    'default' => 1
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                    'default' => ''
                                ],
                                'tsCreated' => [
                                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                                    'null' => false,
                                    'default' => null,
                                ],
                                'tsUpdated' => [
                                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                                    'null' => false,
                                ],
                            ],
                            'constraints' => [
                                'name' => [
                                    'type' => ORM\Mapper\Table\Constraint\UniqueKey::class,
                                    'columns' => ['name'],
                                ]
                            ]
                        ],
                    ],
                    'references' => [
                        'Users@user-groups > !QSystem:Groups@Groups' => [
                            'type' => ORM\Mapper\Reference\Reference::M2M,
                            'via' => 'QSysytem:UsersGroups(iUser, iGroup)',
                            'conditions' => [
                                'QSystem:UsersGroups.active' => 0
                            ]
                        ],
                        // 'Users@User > !UsersProfiles@Profile' => [
                        //     'type' => ORM\Mapper\Reference\Reference::O2O,
                        //     'via' => 'UsersProfiles(iUser)',
                        //     'conditions' => []
                        // ],
                        // 'Groups > Roles@Roles' => [
                        //     'type' => ORM\Mapper\Reference\Reference::M2M,
                        //     'via' => 'GroupsRoles(iGroup, iRole)',
                        //     'conditions' => []
                        // ],
                    ]
                ]
                /* }}} */
            ],
        ];
    }

    /**
     * getAppConfigs
     *
     */
    public function getAppConfigs()
    {
        return [
            'form-manager' => [
                'token-salt' => 'qore-platform',
                'token-key' => '94ee1eca96b94c5f38a87554d3e8d7e4',
            ],
            'project-name' => basename(PROJECT_PATH),
        ];
    }

    /**
     * getTwigOptions
     *
     */
    public function getTwigOptions() : array
    {
        return [
            'cache_dir'      => touch_dir(PROJECT_STORAGE_PATH . DS . 'cache') . DS . 'twig',
            'assets_url'     => '',
            'global_assets_url' => '/static-gateway',
            'assets_version' => null,
            'extensions'     => [
                // extension service names or instances
            ],
            'runtime_loaders' => [
                // runtime loader names or instances
            ],
            'globals' => [
                // Variables to pass to all twig templates
                'Qore' => [ ]
            ],
            'global_objects' => [
                'auth' => Auth\AuthenticationService::class,
                'csrf' => CsrfInterface::class,
            ],
        ];
    }

    /**
     * getRouterConfigs
     *
     */
    public function getRouterConfigs()
    {
        return [
            'fastroute' => [
                'cache_enabled' => true,
                'cache_file' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'router']) . DS . 'fastroute.php.cache',
                'routes_cache_file' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'router']) . DS . 'fastroute.routes'
            ],
        ];
    }

    /**
     * getTemplates
     *
     */
    public function getTemplates() : array
    {
        return [
            'extension' => 'twig'
        ];
    }

    /**
     * getSessionsConfig
     *
     */
    public function getSessionsConfig()
    {
        return [
            'session_config' => [
                // Срок действия cookie сессии истечет через 1 час.
                // 'cookie_lifetime' => 60*60*24,
                // Данные сессии будут храниться на сервере до 30 дней.
                'gc_maxlifetime'     => 60*60*24*30,
                'cookie_samesite' => 'None Secure'
            ],

            // Настройка менеджера сессий.
            'session_manager' => [
                // Валидаторы сессии (используются для безопасности).
                'validators' => [
                    // HttpUserAgent::class,
                ]
            ],

            // Настройка хранилища сессий.
            'session_storage' => [
                'type' => SessionArrayStorage::class
            ],
        ];
    }

}
