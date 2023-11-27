<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

use Qore\ORM\ModelManagerInterface;
use Qore\ORM\Mapper;
use Qore\ORM\Mapper\Table;
use Qore\ORM\Mapper\Reference;

class TableProcessor extends BaseProcessor implements TableProcessorInterface, ReferenceProcessorInterface
{
    /**
     * table
     *
     * @var mixed
     */
    protected $table = null;

    /**
     * repository
     *
     * @var mixed
     */
    protected $repository = null;

    /**
     * gateway
     *
     * @var mixed
     */
    protected $gateway = null;

    /**
     * unqiuePreffix
     *
     * @var mixed
     */
    protected $uniquePreffix = null;

    /**
     * @var ?array
     */
    protected $parsedColumnNames = null;

    /**
     * __construct
     *
     * @param Mapper\Reference\ReferenceInstance $_reference
     * @param ProcessorRepository $_repository
     * @param Gateway $_gateway
     */
    public function __construct(
        Table\TableInterface $_table,
        ProcessorRepository $_repository,
        GatewayInterface $_gateway,
        ModelManagerInterface $_mm,
        string $_processorPath
    )
    {
        $this->table = $_table;
        $this->repository = $_repository;
        $this->gateway = $_gateway;
        $this->mm = $_mm;
        $this->uniquePreffix = $this->generateUnique();
        $this->processorPath = $_processorPath;
    }

    /**
     * getReference
     *
     */
    public function getReference($_reference) : Reference\ReferenceInterface
    {
        return $this->table->getReference($_reference);
    }

    /**
     * getTargetTable
     *
     */
    public function getTargetTable()
    {
        return $this->table;
    }

    /**
     * getTableName
     *
     */
    public function getTableName() : string
    {
        return $this->table->getTableName();
    }

    /**
     * getEntityName
     *
     */
    public function getEntityName() : string
    {
        return $this->table->getEntityName();
    }

    /**
     * getTableAlias
     *
     */
    public function getTableAlias() : string
    {
        return $this->prepareSelectTable($this->table);
    }

    /**
     * getTableReferences
     *
     */
    public function getTableReferences() : array
    {
        return $this->table->getReferences();
    }

    /**
     * getColumns
     *
     */
    public function getColumns()
    {
        return $this->table->getColumns();
    }

    /**
     * prepareSelect
     *
     */
    public function prepareSelect() : void
    {
        $select = $this->gateway->select();

        $select->withAutoColumns() && $select->columns(
            array_merge($select->columns, $this->prepareSelectColumns($this->table))
        );

        if ($entities = $this->repository->getAll()) {
            $whereCursor = $this->gateway->getWhereCursor($this->processorPath);
            $entitiesID = [];
            foreach ($entities as $entity) {
                if (! $entity->isNew()) {
                    $entitiesID[] = $entity->id;
                }
            }

            if ($entitiesID) {
                $whereCursor(function($_where) use ($entitiesID) {
                    $_where(['@this.id' => $entitiesID]);
                });
            }
        }

        if (! $select->isTableReadOnly()) {
            $select->from([$this->prepareSelectTable($this->table) => $this->getTableName()]);
        }
    }

    /**
     * parseResult
     *
     * @param array $_row
     */
    public function parseResult(array &$_row) : void
    {
        # - Get this columns
        $columns = [];
        foreach($this->table->getColumns() as $column) {
            $columns[$column->getName()] = $column->getAlias();
        }

        if (is_null($this->parsedColumnNames)) {
            $this->parseColumnNames($_row);
        }

        $entity = [];
        /** attach all columns if columns names is matched */
        foreach ($this->parsedColumnNames as $sqlColumn => $column) {
            $entity[$columns[$column] ?? $column] = $_row[$sqlColumn];
        }

        $this->setEntityToRepository($entity, true);
    }

    /**
     * Get processor subject alias replacements
     *
     * @param Table\Column\ColumnInterface $_column
     */
    public function prepareSelectColumnAlias(Table\Column\ColumnInterface $_column) : string
    {
        return $this->prepareSelectTable($_column->getTable()) . '.' . $_column->getName();
    }

    /**
     * getProcessorReplacements
     *
     * @param string $_processorPath
     */
    public function getProcessorReplacements(string $_processorPath, bool $_useAlias = true) : array
    {
        $table = $_useAlias ? $this->getTableAlias() : $this->getTableName();

        $return = [$_processorPath => $table];
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
     * prepareSelectColumns
     *
     * @param Table\Table $_table
     */
    protected function prepareSelectColumns(Table\Table $_table, array $_columns = []) : array
    {
        $columns = $_table->getColumns();
        if ($_columns) {
            $columns = array_filter($columns, function($_column) use ($_columns) {
                return in_array($_column->getName(), $_columns);
            });
        }

        $return = [];
        foreach ($columns as $column) {
            $return[$this->prepareSelectColumnAlias($column)] = $column->getName();
        }

        return $return;
    }

}
