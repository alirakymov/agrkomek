<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

use Qore\ORM\ModelManagerInterface;
use Qore\ORM\Mapper;
use Qore\ORM\Mapper\Table;
use Qore\ORM\Mapper\Reference;
use Qore\ORM\Entity\EntityInterface;
use Qore\Collection\Collection;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Predicate;
use Qore\Qore;

/**
 * Class: ReferenceProcessor
 *
 * @see ReferenceProcessorInterface
 * @see BaseProcessor
 */
class ReferenceProcessor extends BaseProcessor implements ReferenceProcessorInterface
{
    /**
     * reference
     *
     * @var mixed
     */
    protected $reference = null;

    /**
     * repository
     *
     * @var mixed
     */
    protected $repository = null;

    /**
     * parentProcessor
     *
     * @var mixed
     */
    protected $parentProcessor = null;

    /**
     * gateway
     *
     * @var mixed
     */
    protected $gateway = null;

    /**
     * referenceKeysMap
     *
     * @var mixed
     */
    protected $referenceKeysMap = [];

    /**
     * unqiuePreffix
     *
     * @var mixed
     */
    protected $uniquePreffix = null;

    /**
     * unlinkedKeysMap
     *
     * @var mixed
     */
    protected $unlinkedKeysMap = [];

    /**
     * @var array - cached column aliases
     */
    protected $cachedColumnAliases = [];

    /**
     * @var array|null
     */
    protected $preparedParseStructure = null;

    /**
     * __construct
     *
     * @param Mapper\Reference\ReferenceInterface $_reference
     * @param ProcessorRepository $_repository
     * @param ReferenceProcessorInterface $_parentProcessor
     * @param GatewayInterface $_gateway
     * @param ModelManagerInterface $_mm
     */
    public function __construct(
        Mapper\Reference\ReferenceInterface $_reference,
        ProcessorRepository $_repository = null,
        ReferenceProcessorInterface $_parentProcessor,
        GatewayInterface $_gateway,
        ModelManagerInterface $_mm,
        string $_processorPath
    )
    {
        $this->reference = $_reference;
        $this->parentProcessor = $_parentProcessor;
        $this->gateway = $_gateway;
        $this->mm = $_mm;
        $this->repository = $_repository ?? $this->mm->getProcessorRepository(
            $this->getTargetTable()->getEntityName()
        );
        $this->uniquePreffix = $this->generateUnique();
        $this->processorPath = $_processorPath;
    }

    /**
     * setRepository
     *
     * @param Gateway\Repository $_repository
     */
    public function setRepository(ProcessorRepositoryInterface $_repository)
    {
        $this->repository = $_repository;
    }

    /**
     * getRepository
     *
     */
    public function getRepository() : ProcessorRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * prepareSql
     *
     */
    public function prepareSelect() : void
    {
        $select = $this->gateway->select();
        $referenceMap = $this->reference->getReferenceMap();
        $conditions = $this->reference->getConditions();

        $currentColumn = array_shift($referenceMap);
        $currentTableAlias = $this->gateway->getProcessor()->getTableAlias();

        $targetColumn = array_pop($referenceMap);
        $targetTable = $targetColumn->getTable();

        if ($referenceMap) {

            reset($referenceMap);
            while ($column = current($referenceMap)) {

                $columnTable = $column->getTable();
                $tableAlias = $this->prepareSelectTable($columnTable);

                $predicates = [
                    new Predicate\Operator(
                        vsprintf('%s.%s', [ $currentTableAlias, $currentColumn->getName() ]),
                        '=',
                        vsprintf('%s.%s', [ $tableAlias, $column->getName() ]),
                        Predicate\Operator::TYPE_IDENTIFIER,
                        Predicate\Operator::TYPE_IDENTIFIER
                    )
                ];

                if (isset($conditions[$columnTable->getEntityName()])) {
                    foreach ($conditions[$columnTable->getEntityName()] as $condition) {
                        if (is_object($condition[1]) && $condition[1] instanceof Table\Column\ColumnInterface) {
                            $predicates[] = new Predicate\Operator(
                                vsprintf('%s.%s', [$this->prepareSelectTable($condition[0]->getTable()), $condition[0]->getName()]),
                                '=',
                                vsprintf('%s.%s', [$this->prepareSelectTable($condition[1]->getTable), $condition[1]->getName()]),
                                Predicate\Operator::TYPE_IDENTIFIER,
                                Predicate\Operator::TYPE_IDENTIFIER
                            );
                        } else {
                            $predicates[] = new Predicate\Operator(
                                vsprintf('%s.%s', [$this->prepareSelectTable($condition[0]->getTable()), $condition[0]->getName()]),
                                '=',
                                vsprintf('%s', [$condition[1]]),
                                Predicate\Operator::TYPE_IDENTIFIER,
                                Predicate\Operator::TYPE_VALUE
                            );
                        }
                    }
                }

                $predicateSet = new Predicate\Predicate();
                $predicateSet->addPredicates($predicates, $predicateSet::OP_AND);

                $select->join(
                    [$tableAlias => $columnTable->getTableName()],
                    $predicateSet,
                    $this->prepareSelectColumns($columnTable),
                    Join::JOIN_LEFT
                );

                # - If the tables of the current and next fields are identical, then go to the next field.
                if (($nextColumn = next($referenceMap)) && $nextColumn->getTable() == $columnTable) {
                    $currentColumn = $nextColumn;
                    next($referenceMap);
                # - If the tables of the current and next fields aren't identical, then use standart column to reference and go back.
                } elseif ($nextColumn) {
                    $currentColumn = prev($referenceMap)->getTable()->getColumn('id');
                }

                $currentTableAlias = $this->prepareSelectTable($currentColumn->getTable());
            }
        }

        # - Join target table
        $tableAlias = $this->prepareSelectTable($targetTable);
        $predicates = [
            new Predicate\Operator(
                vsprintf('%s.%s', [$currentTableAlias, $currentColumn->getName()]),
                '=',
                vsprintf('%s.%s', [$tableAlias, $targetColumn->getName()]),
                Predicate\Operator::TYPE_IDENTIFIER,
                Predicate\Operator::TYPE_IDENTIFIER
            )
        ];

        if (isset($conditions[$targetTable->getEntityName()])) {
            foreach ($conditions[$targetTable->getEntityName()] as $condition) {
                if (is_object($condition[1]) && $condition[1] instanceof Table\Column\ColumnInterface) {
                    $predicates[] = new Predicate\Operator(
                        vsprintf('`%s`.%s', [$this->prepareSelectTable($condition[0]->getTable()), $condition[0]->getName()]),
                        '=',
                        vsprintf('%s.%s', [$this->prepareSelectTable($condition[1]->getTable), $condition[1]->getName()]),
                        Predicate\Operator::TYPE_IDENTIFIER,
                        Predicate\Operator::TYPE_IDENTIFIER
                    );
                } else {
                    $predicates[] = new Predicate\Operator(
                        vsprintf('%s.%s', [$this->prepareSelectTable($condition[0]->getTable()), $condition[0]->getName()]),
                        '=',
                        vsprintf('%s', [$condition[1]]),
                        Predicate\Operator::TYPE_IDENTIFIER,
                        Predicate\Operator::TYPE_VALUE
                    );
                }
            }
        }

        $predicateSet = new Predicate\Predicate();
        $predicateSet->addPredicates($predicates, $predicateSet::OP_AND);

        $select->join(
            [$tableAlias => $targetTable->getTableName()],
            $predicateSet,
            $this->prepareSelectColumns($targetTable),
            Join::JOIN_LEFT
        );
    }

    /**
     * parseResult
     *
     * @param array $_row
     */
    public function parseResult(array &$_row) : void
    {
        $referenceMap = $this->reference->getReferenceMap();

        if (is_null($this->preparedParseStructure)) {
            if (count($referenceMap) === 2) {
                $this->preparedParseStructure = [
                    $referenceMap[1],
                    $this->prepareSelectColumnAlias($referenceMap[1]),
                    $this->parentProcessor->prepareSelectColumnAlias(
                        $referenceMap[0]->getTable()->getColumn('id')
                    ),
                ];
            } elseif(count($referenceMap) === 4) {
                $this->preparedParseStructure = [
                    $referenceMap[3],
                    $this->prepareSelectColumnAlias($referenceMap[3]),
                    $this->prepareSelectColumnAlias($referenceMap[1]),
                ];
            }
        }

        list($targetColumn, $targetColumnAlias, $referenceColumnAlias) = $this->preparedParseStructure;

        if (! $_row[$referenceColumnAlias] || ! $_row[$targetColumnAlias]) {
            return;
        }

        if (is_null($this->parsedColumnNames)) {
            $this->parseColumnNames($_row);
        }

        $columns = $targetColumn->getTable()->getColumnAliases();

        $entity = [];
        /** attach all columns if columns names is matched */
        foreach ($this->parsedColumnNames as $sqlColumn => $column) {
            $entity[$columns[$column] ?? $column] = $_row[$sqlColumn];
        }

        $entity = $this->setEntityToRepository($entity, true);

        $this->setReferenceKeysMap((int)$_row[$referenceColumnAlias], $entity);
    }

    /**
     * compareEntity
     *
     */
    public function compareEntity(EntityInterface $_entity) : void
    {
        $relatedEntities = [];
        if ($relatedEntities = $this->getRelatedEntities($_entity)) {
            $this->gateway->compareEntities($relatedEntities);
        }

        $_entity->setRelatedEntities($this->reference->getReferenceName(), $relatedEntities);
    }

    /**
     * getTargetTable
     *
     */
    public function getTargetTable() : Table\Table
    {
        $referenceMap = $this->reference->getReferenceMap();
        return array_pop($referenceMap)->getTable();
    }

    /**
     * getTableAlias
     *
     */
    public function getTableAlias() : string
    {
        $referenceMap = $this->reference->getReferenceMap();
        return $this->prepareSelectTable(array_pop($referenceMap)->getTable());
    }

    /**
     * Get processor subject alias replacements
     *
     * @param string $_processorPath
     * @param bool $_useAlias (optional)
     *
     * @return array
     */
    public function getProcessorReplacements(string $_processorPath, bool $_useAlias = true): array
    {
        $table = $_useAlias ? $this->getTableAlias() : $this->getTableName();

        $return = [$_processorPath => $table];
        if ($this->reference->getReferenceType() === Reference\Reference::M2M) {
            $referenceMap = $this->reference->getReferenceMap();
            $return[$_processorPath . '.reference'] = $this->prepareSelectTable(array_values($referenceMap)[1]->getTable());
        }

        foreach ($this->getColumns() as $column) {
            $return[sprintf('%s.%s', $_processorPath, $column->getAlias())] = sprintf(
                '%s.%s',
                $table,
                $column->getName()
            );
        }

        return $return;
    }

    /**
     * getTableName
     *
     */
    public function getTableName() : string
    {
        return $this->getTargetTable()->getTableName();
    }

    /**
     * getColumns
     *
     */
    public function getColumns() : array
    {
        return $this->getTargetTable()->getColumns();
    }

    /**
     * getTableReferences
     *
     */
    public function getTableReferences() : array
    {
        return $this->getTargetTable()->getReferences();
    }

    /**
     * getReference
     *
     */
    public function getReference($_reference) : Reference\ReferenceInterface
    {
        return $this->getTargetTable()->getReference($_reference);
    }

    /**
     * setEntitiesToRepository
     *
     * @param array $entities
     */
    public function setEntitiesToRepository(array $_entities) : void
    {
        foreach ($_entities as $referenceId => $referencedEntities) {
            foreach ($referencedEntities as $entity) {
                $entity = $this->setEntityToRepository($entity);
                $this->setReferenceKeysMap($referenceId, $entity);
            }
        }
    }

    /**
     * unlinkEntities
     *
     * @param array $_unlinkedEntities
     */
    public function unlinkEntities(array $_unlinkedEntities) : void
    {
        $this->unlinkedKeysMap = $_unlinkedEntities;
    }

    /**
     * prepareSelectColumnAlias
     *
     * @param Table\Column\ColumnInterface $_column
     */
    public function prepareSelectColumnAlias(Table\Column\ColumnInterface $_column) : string
    {
        $id = spl_object_id($_column);
        if (! isset($this->cachedColumnAliases[$id])) {
            $this->cachedColumnAliases[$id] = $this->prepareSelectTable($_column->getTable()) . '.' . $_column->getName();
        }

        return $this->cachedColumnAliases[$id];
    }

    /**
     * setReferenceKeysMap
     *
     * @param mixed $_reference
     * @param mixed $_entity
     */
    protected function setReferenceKeysMap($_reference, EntityInterface $_entity) : void
    {
        if (! isset($this->referenceKeysMap[$_reference])) {
            $this->referenceKeysMap[$_reference] = [];
        }

        if (! in_array($_entity, $this->referenceKeysMap[$_reference])) {
            $this->referenceKeysMap[$_reference][] = $_entity;
        }
    }

    /**
     * getRelatedEntities
     *
     * @param mixed $_entity
     */
    protected function getRelatedEntities($_entity) : array
    {
        $return = [];

        if (isset($this->referenceKeysMap[$_entity->id])) {
            foreach ($this->referenceKeysMap[$_entity->id] as $entity) {
                $return[$entity->id] = $entity;
            }
        }

        return $return;
    }

    /**
     * prepareSelectColumns
     *
     * @param Table\Table $_table
     * @param array $_columns
     * @param mixed $_alias
     */
    protected function prepareSelectColumns(Table\Table $_table, array $_columns = [], $_aliases = false) : array
    {

        $select = $this->gateway->select();
        if (! $select->withAutoColumns()) {
            return [];
        }

        $columns = $_table->getColumns();
        if ($_columns) {
            $columns = array_filter($columns, function($_column) use ($_columns) {
                return in_array($_column->getName(), $_columns);
            });
        }

        $return = [];
        foreach ($columns as $column) {
            $return[$this->prepareSelectColumnAlias($column)] = $_aliases ? $column->getAlias() : $column->getName();
        }

        return $return;
    }

}
