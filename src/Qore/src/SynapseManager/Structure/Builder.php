<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Structure;

use Qore\ORM;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\SynapseManager;
use Qore\Collection\Collection;
use Psr\Container\ContainerInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class: Builder
 *
 * @see BuilderInterface
 */
class Builder implements BuilderInterface
{
    /**
     * sm
     *
     * @var Qore\SynapseManager\SynapseManager
     */
    private $sm = null;

    /**
     * sructure
     *
     * @var array
     */
    private $structure = [];

    /**
     * structureForMapperDriver
     *
     * @var mixed
     */
    private $structureForMapperDriver = [];

    /**
     * container
     *
     * @var mixed
     */
    private $container = null;

    /**
     * __construct
     *
     */
    public function __construct(ContainerInterface $_container)
    {
        $this->container = $_container;
    }

    /**
     * setSynapseManager
     *
     * @param SynapseManager $_sm
     */
    public function setSynapseManager(SynapseManager $_sm)
    {
        $this->sm = $_sm;
    }

    /**
     * loadStructure
     *
     */
    public function loadStructure() : Builder
    {
        $mm = $this->sm->getModelManager();

        $this->structure = $mm('QSynapse:Synapses')
            ->with('attributes')
            ->with('relationsFrom', function($_gw){
                $_gw->with('synapseTo');
            })->with('relationsTo', function($_gw){
                $_gw->with('synapseFrom');
            })->all();

        return $this;
    }

    /**
     * getStructureForMapperDriver
     *
     */
    public function getStructureForMapperDriver()
    {
        if (! $this->structureForMapperDriver) {
            $this->loadStructureForMapperDriver();
        }

        return $this->structureForMapperDriver;
    }

    /**
     * loadStructureForMapperDriver
     *
     */
    protected function loadStructureForMapperDriver()
    {
        $config = $this->container->get('config');
        $filePath = $config['qore']['synapse-configs']['structure-cache-file'] ?? false;

        if ($filePath && is_file($filePath)) {
            $this->structureForMapperDriver = require $filePath;
        } else {
            $this->buildStructureForMapperDriver();
            $filePath && file_put_contents($filePath, str_replace(
                '{config}',
                VarExporter::export($this->structureForMapperDriver),
                $this->getConfigSnippet()
            ));
        }
    }

    /**
     * buildStructureForMapperDriver
     *
     */
    private function buildStructureForMapperDriver()
    {
        if (! $this->structure) {
            $this->loadStructure();
        }

        $tables = [];
        $references = [];
        $attributeTypes = Entity\SynapseAttribute::getTypes();

        foreach ($this->structure as $synapse) {

            $columns = [
                '__iSynapseService' => [
                    'type' => ORM\Mapper\Table\Column\Integer::class,
                    'length' => 11,
                    'null' => true,
                    'default' => 0
                ],
                '__idparent' => [
                    'type' => ORM\Mapper\Table\Column\Integer::class,
                    'length' => 11,
                    'null' => false,
                    'default' => 0
                ],
                '__options' => [
                    'type' => ORM\Mapper\Table\Column\Text::class,
                    'null' => true,
                    'default' => ''
                ],
                '__created' => [
                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                    'null' => true,
                ],
                '__updated' => [
                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                    'null' => true,
                ],
                '__indexed' => [
                    'type' => ORM\Mapper\Table\Column\Integer::class,
                    'length' => 1,
                    'null' => false,
                    'default' => 0
                ],
                '__deleted' => [
                    'type' => ORM\Mapper\Table\Column\Timestamp::class,
                    'null' => true,
                ],
            ];


            $constraints = [
                'parent' => [
                    'type' => ORM\Mapper\Table\Constraint\Index::class,
                    'columns' => ['__idparent'],
                ],
                'created' => [
                    'type' => ORM\Mapper\Table\Constraint\Index::class,
                    'columns' => ['__created'],
                ],
                'updated' => [
                    'type' => ORM\Mapper\Table\Constraint\Index::class,
                    'columns' => ['__updated'],
                ],
                'indexed' => [
                    'type' => ORM\Mapper\Table\Constraint\Index::class,
                    'columns' => ['__indexed'],
                ],
                'deleted' => [
                    'type' => ORM\Mapper\Table\Constraint\Index::class,
                    'columns' => ['__deleted'],
                ],
            ];

            if ($synapse->attributes) {
                foreach ($synapse->attributes as $attribute) {
                    $columns['attribute-' . $attribute->id] = array_merge($attributeTypes[$attribute->type], [
                        'type' => $attribute->type,
                        'alias' => $attribute->name,
                        'null' => true,
                    ]);
                }
            }

            list($synapseClassName, $synapseReferenceClassName) = $this->getSynapseEntityClass($synapse->name);

            $tables[$synapse->name] = [
                'entity' => $synapseClassName,
                'columns' => $columns,
                'constraints' => $constraints,
            ];

            $tables[$synapse->name . '_References'] = [
                'entity' => $synapseReferenceClassName,
                'columns' => [
                    'iSynapseRelation' => [
                        'type' => ORM\Mapper\Table\Column\Integer::class,
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseEntityFrom' => [
                        'type' => ORM\Mapper\Table\Column\Integer::class,
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceFrom' => [
                        'type' => ORM\Mapper\Table\Column\Integer::class,
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    'iSynapseEntityTo' => [
                        'type' => ORM\Mapper\Table\Column\Integer::class,
                        'length' => 11,
                        'null' => false,
                    ],
                    'iSynapseServiceTo' => [
                        'type' => ORM\Mapper\Table\Column\Integer::class,
                        'length' => 11,
                        'null' => false,
                        'default' => 0,
                    ],
                    '__created' => [
                        'type' => ORM\Mapper\Table\Column\Timestamp::class,
                        'null' => true,
                    ],
                    '__updated' => [
                        'type' => ORM\Mapper\Table\Column\Timestamp::class,
                        'null' => true,
                    ],
                    '__deleted' => [
                        'type' => ORM\Mapper\Table\Column\Timestamp::class,
                        'null' => true,
                    ],
                ],
                'constraints' => [
                    'iSR-iSEF-iSET' => [
                        'type' => ORM\Mapper\Table\Constraint\Index::class,
                        'columns' => ['iSynapseRelation', 'iSynapseEntityFrom', 'iSynapseEntityTo'],
                    ],
                    'iSR-iSET-iSEF' => [
                        'type' => ORM\Mapper\Table\Constraint\Index::class,
                        'columns' => ['iSynapseRelation', 'iSynapseEntityTo', 'iSynapseEntityFrom'],
                    ],
                    'iSR-iSEF-iSSF-iSET-iSST' => [
                        'type' => ORM\Mapper\Table\Constraint\UniqueKey::class,
                        'columns' => ['iSynapseRelation', 'iSynapseEntityFrom', 'iSynapseServiceFrom', 'iSynapseEntityTo', 'iSynapseServiceTo'],
                    ],
                    'iSR-iSET-iSST-iSEF-iSSF' => [
                        'type' => ORM\Mapper\Table\Constraint\UniqueKey::class,
                        'columns' => ['iSynapseRelation', 'iSynapseEntityTo', 'iSynapseServiceTo', 'iSynapseEntityFrom', 'iSynapseServiceFrom'],
                    ],
                ]
            ];

            foreach ($synapse->relationsFrom as $relation) {
                if (! $relation->synapseTo) continue;
                $referenceTableName = $synapse->name . '_References';
                $references[$this->getSynapseRelationRule($synapse, $relation)] = [
                    'type' => ORM\Mapper\Reference\Reference::M2M,
                    'decorate-type' => $relation['type'],
                    'via' => vsprintf('%s(%s,%s)', [
                        $referenceTableName,
                        'iSynapseEntityFrom',
                        'iSynapseEntityTo',
                    ]),
                    'conditions' => [
                        $referenceTableName . '.iSynapseRelation' => $relation->id
                    ],
                ];
            }
        }

        $this->structureForMapperDriver = [
            'metadata' => [
                'tables' => $tables,
                'references' => $references,
            ]
        ];
    }

    /**
     * getSynapseEntityClass
     *
     * @param string $_synapseName
     */
    private function getSynapseEntityClass(string $_synapseName)
    {
        $config = $this->sm->getContainer()->get('config');
        $synapseClassName = null;
        $synapseReferenceClassName = null;
        if ($namespaces = $config['qore']['synapse-configs']['namespaces'] ?? []) {
            foreach ($namespaces as $namespace) {
                $synapseClassTemplate = $namespace . '\\' . $_synapseName . '\\%s';
                if (class_exists($synapseClassName = sprintf($synapseClassTemplate, $_synapseName))
                    || class_exists($synapseClassName = sprintf($synapseClassTemplate, 'SynapseEntity'))) {
                    break;
                } else {
                    $synapseClassName = null;
                }
            }

            foreach ($namespaces as $namespace) {
                $synapseReferenceClassTemplate = $namespace . '\\' . $_synapseName . '\\%s';
                if (class_exists($synapseReferenceClassName = sprintf($synapseReferenceClassTemplate, $_synapseName . 'Reference'))
                    || class_exists($synapseReferenceClassName = sprintf($synapseReferenceClassTemplate, 'SynapseReferenceEntity'))) {
                    break;
                } else {
                    $synapseReferenceClassName = null;
                }
            }
        }

        return [
            $synapseClassName ?? Entity\SynapseBaseEntity::class,
            $synapseReferenceClassName ?? Entity\SynapseReferenceBaseEntity::class
        ];
    }

    /**
     * getSynapseRelationRule
     *
     * @param ORM\Entity\EntityInterface $_synapse
     * @param ORM\Entity\EntityInterface $_relation
     */
    private function getSynapseRelationRule(ORM\Entity\EntityInterface $_synapse, ORM\Entity\EntityInterface $_relation)
    {
        return $_synapse->name . '@' . ($_relation->synapseAliasTo ?: $_synapse->name)
            . ' > '
            . $_relation->synapseTo->name . '@' . ($_relation->synapseAliasFrom ?: $_relation->synapseTo->name);
    }

    /**
     * getConfigSnippet
     *
     */
    protected function getConfigSnippet()
    {
        return <<<EOT
<?php

return {config};
EOT;
    }

}
