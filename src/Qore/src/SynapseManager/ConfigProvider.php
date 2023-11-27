<?php

declare(strict_types=1);

namespace Qore\SynapseManager;

use Qore\ORM;
use DirectoryIterator;
use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\HttpUserAgent;
use Qore\ORM\Mapper\Table\Column\BigInteger;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Decimal;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\ORM\Mapper\Table\Column\LongText;
use Qore\ORM\Mapper\Table\Column\Text;
use Qore\ORM\Mapper\Table\Column\Varchar;
use Twig;
use Mezzio\MiddlewareContainer;
use Mezzio\Twig\TwigExtension;
use Mezzio\Router\FastRouteRouter;
use Psr\Http\Message\ServerRequestInterface;

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
                'console' => $this->getConsoleOptions(),
            ]
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
            'invokables' => [],
            'aliases' => [
                Plugin\Filter\Filter::class => Plugin\Filter\FilterInterface::class,
            ],
            'abstract_factories' => [],
            'factories' => [
                SynapseManager::class => SynapseManagerFactory::class,
                Structure\Builder::class => Structure\BuilderFactory::class,
                Artificer\Service\Repository::class => Artificer\RepositoryFactory::class,
                Artificer\Service\ServiceArtificer::class => Artificer\ArtificerFactory::class,
                Artificer\RepositoryCollectionLoader::class => Artificer\RepositoryCollectionLoaderFactory::class,
                Artificer\Form\Repository::class => Artificer\RepositoryFactory::class,
                Artificer\Form\FormArtificer::class => Artificer\ArtificerFactory::class,
                Artificer\Decorator\TileComponent::class => Artificer\Decorator\TileComponentFactory::class,
                Artificer\Decorator\ListComponent::class => Artificer\Decorator\ListComponentFactory::class,
                Plugin\PluginProvider::class => Plugin\PluginProviderFactory::class,
                Plugin\Filter\FilterInterface::class => Plugin\Filter\FilterFactory::class,
                Plugin\Indexer\Indexer::class => Plugin\Indexer\IndexerFactory::class,
                Plugin\Report\Report::class => Plugin\Report\ReportFactory::class,
                Plugin\Indexer\SearchEngineInterface::class => Plugin\Indexer\SearchEngineFactory::class,
                Plugin\FormMaker\FormMaker::class => Plugin\FormMaker\FormMakerFactory::class,
                Plugin\RoutingHelper\RoutingHelper::class => Plugin\RoutingHelper\RoutingHelperFactory::class,
                Plugin\Designer\Designer::class => Plugin\Designer\DesignerFactory::class,
                Plugin\Designer\InterfaceGateway\FormDecorator::class => Plugin\Designer\InterfaceGateway\FormDecoratorFactory::class,
                Plugin\Designer\InterfaceGateway\FormViewerDecorator::class => Plugin\Designer\InterfaceGateway\FormViewerDecoratorFactory::class,
                Plugin\Chain\Chain::class => Plugin\Chain\ChainFactory::class,
                Plugin\Operation\Operation::class => Plugin\Operation\OperationFactory::class,
                Plugin\Operation\TaskProcess::class => Plugin\Operation\TaskProcessFactory::class,

            ],
            'shared' => [
                Plugin\Designer\InterfaceGateway\FormDecorator::class => false,
                Plugin\Designer\InterfaceGateway\FormViewerDecorator::class => false,
                Plugin\Operation\Operation::class => false,
                Plugin\Operation\TaskProcess::class => false,
                Plugin\Indexer\SearchEngineInterface::class => false,
                Plugin\Filter\FilterInterface::class => false,
                Artificer\Decorator\TileComponent::class => false,
                Artificer\Decorator\ListComponent::class => false,
            ],
            'initializers' => [],
            'initializer-targets' => [],
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
            'synapse-configs' => [
                'namespaces' => [
                    '\\Qore\\App\\SynapseNodes\\System',
                    '\\Qore\\App\\SynapseNodes\\Components',
                ],
                'structure-cache-file' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'synapse-system']) . DS . 'synapse-structure.php',
                'services-collection-cache-file' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'synapse-system']) . DS . 'services-collection',
                'forms-collection-cache-file' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'synapse-system']) . DS . 'forms-collection',
                'data-synchronizer' => $this->getDataSynchronizerOptions(),
                'code-builder' => $this->getCodeBuilderOptions(),
                'indexer' => [
                    'mapping' => [
                        'types' => [
                            BigInteger::class => [ 'type' => 'bigint' ],
                            Integer::class => [ 'type' => 'int' ],
                            Decimal::class => [ 'type' => 'float' ],
                            Datetime::class => [ 'type' => 'timestamp', ],
                            Varchar::class => [ 'type' => 'text' ],
                            Text::class => [ 'type' => 'text' ],
                            LongText::class => [ 'type' => 'text' ],
                        ]
                    ]
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getConsoleOptions() : array
    {
        return [
            'commands' => [
                'Qore\\SynapseManager\\Plugin\\Operation\\TaskProcess',
            ],
        ];
    }

    /**
     * getDataSynchronizerOptions
     *
     */
    private function getDataSynchronizerOptions() : array
    {
        return [
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
        ];
    }

    /**
     * getCodeBuilderOptions
     *
     */
    private function getCodeBuilderOptions() : array
    {
        return [
            'templates-path' => [touch_dir([QORE_PATH, 'data', 'synapse-manager', 'templates'])],
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
            'QSynapse' => [
                'metadata' => [
                    'tables' => [
                        'Synapses' => [
                            'entity' => Structure\Entity\Synapse::class,
                            'columns' => [
                                'iParent' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => false,
                                    'default' => 0
                                ],
                                'name' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                                'tree' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                    'default' => 0,
                                ],
                            ]
                        ],
                        'SynapseAttributes' => [
                            'entity' => Structure\Entity\SynapseAttribute::class,
                            'columns' => [
                                'iSynapse' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'name' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'label' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'type' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                            ]
                        ],
                        'SynapseRelations' => [
                            'entity' => Structure\Entity\SynapseRelation::class,
                            'columns' => [
                                'iSynapseFrom' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'synapseAliasFrom' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'iSynapseTo' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'synapseAliasTo' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'type' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                            ]
                        ],
                        'SynapseServices' => [
                            'entity' => Structure\Entity\SynapseService::class,
                            'columns' => [
                                'iSynapse' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'name' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'label' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'index' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                    'default' => 0,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                            ]
                        ],
                        'SynapseServiceSubjects' => [
                            'entity' => Structure\Entity\SynapseServiceSubject::class,
                            'columns' => [
                                'iSynapseRelation' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'iSynapseServiceFrom' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'iSynapseServiceTo' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'relationType' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'default' => 0,
                                    'null' => false,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                            ]
                        ],
                        'SynapseServiceForms' => [
                            'entity' => Structure\Entity\SynapseServiceForm::class,
                            'columns' => [
                                'iSynapseService' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                ],
                                'name' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'label' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'template' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => true,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => false,
                                ],
                                'type' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                ],
                                '__options' => [
                                    'type' => ORM\Mapper\Table\Column\LongText::class,
                                    'null' => false,
                                ],
                            ]
                        ],
                        'SynapseServiceFormFields' => [
                            'entity' => Structure\Entity\SynapseServiceFormField::class,
                            'columns' => [
                                'iSynapseServiceForm' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'iSynapseServiceSubject' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'iSynapseServiceSubjectForm' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'iSynapseAttribute' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true
                                ],
                                'type' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 1,
                                    'null' => false,
                                ],
                                'attributeFieldType' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => true,
                                ],
                                'label' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'placeholder' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => false,
                                ],
                                'description' => [
                                    'type' => ORM\Mapper\Table\Column\Text::class,
                                    'null' => true,
                                ],
                            ],
                        ],
                        'SynapsePluginIndexer' => [
                            'entity' => Plugin\Indexer\SynapsePluginIndexer::class,
                            'columns' => [
                                'iSynapseService' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'mappingState' => [
                                    'type' => ORM\Mapper\Table\Column\LongText::class,
                                    'null' => false,
                                ],
                                'lastIndexDate' => [
                                    'type' => ORM\Mapper\Table\Column\Datetime::class,
                                    'null' => false,
                                ],
                            ],
                        ],
                        'SynapsePluginReport' => [
                            'entity' => Plugin\Report\SynapsePluginReport::class,
                            'columns' => [
                                'iSynapseService' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'fileUnique' => [
                                    'type' => ORM\Mapper\Table\Column\Varchar::class,
                                    'length' => 255,
                                    'null' => true,
                                    'default' => '',
                                ],
                                'counted' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'processed' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'idLast' => [
                                    'type' => ORM\Mapper\Table\Column\Integer::class,
                                    'length' => 11,
                                    'null' => true,
                                    'default' => 0
                                ],
                                'filters' => [
                                    'type' => ORM\Mapper\Table\Column\LongText::class,
                                    'null' => false,
                                ],
                                'completed' => [
                                    'type' => ORM\Mapper\Table\Column\Datetime::class,
                                    'null' => false,
                                ],
                            ],
                        ],
                    ],
                    'references' => [
                        'Synapses@synapse > !SynapseAttributes@attributes' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseAttributes(iSynapse)',
                            'conditions' => [],
                        ],
                        'Synapses@synapseFrom > !SynapseRelations@relationsFrom' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseRelations(iSynapseFrom)',
                            'conditions' => [],
                        ],
                        'Synapses@synapseTo > !SynapseRelations@relationsTo' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseRelations(iSynapseTo)',
                            'conditions' => [],
                        ],
                        'Synapses@synapse > !SynapseServices@services' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServices(iSynapse)',
                            'conditions' => [],
                        ],
                        'SynapseRelations@relation > !SynapseServiceSubjects@subjects' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceSubjects(iSynapseRelation)',
                            'conditions' => [],
                        ],
                        'SynapseServices@serviceFrom > !SynapseServiceSubjects@subjectsFrom' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceSubjects(iSynapseServiceFrom)',
                            'conditions' => [],
                        ],
                        'SynapseServices@serviceTo > !SynapseServiceSubjects@subjectsTo' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceSubjects(iSynapseServiceTo)',
                            'conditions' => [],
                        ],
                        'SynapseServices@service > !SynapseServiceForms@forms' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceForms(iSynapseService)',
                            'conditions' => [],
                        ],
                        'SynapseServiceForms@form > !SynapseServiceFormFields@fields' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceFormFields(iSynapseServiceForm)',
                            'conditions' => [],
                        ],
                        'SynapseServiceSubjects@relatedSubject > !SynapseServiceFormFields@relatedFields' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceFormFields(iSynapseServiceSubject)',
                            'conditions' => [],
                        ],
                        'SynapseServiceForms@relatedForm > !SynapseServiceFormFields@relatedFields' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceFormFields(iSynapseServiceSubjectForm)',
                            'conditions' => [],
                        ],
                        'SynapseAttributes@relatedAttribute > !SynapseServiceFormFields@relatedFields' => [
                            'type' => ORM\Mapper\Reference\Reference::O2M,
                            'via' => 'SynapseServiceFormFields(iSynapseAttribute)',
                            'conditions' => [],
                        ],
                        'SynapseServices@service > !SynapsePluginIndexer@index' => [
                            'type' => ORM\Mapper\Reference\Reference::O2O,
                            'via' => 'SynapsePluginIndexer(iSynapseService)',
                            'conditions' => [],
                        ],
                    ]
                ]
            ]
        ];
    }

}
