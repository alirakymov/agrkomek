<?php

declare(strict_types=1);

namespace Qore\ORM\Sql;

use Laminas\Db\Sql\Exception;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\ExpressionInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Predicate;
use Laminas\Db\Sql\Sql as LaminasSql;
use Laminas\Db\Sql\SqlInterface;
use Laminas\Db\Sql\Update;
use Qore\ORM\Gateway\Gateway;

class Sql extends LaminasSql
{
    /**
     * gateway
     *
     * @var mixed
     */
    private $gateway = null;

    /**
     * insert
     *
     * @param  mixed $table
     */
    public function insert($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Insert(($table) ?: $this->table);
    }

    /**
     * truncate
     *
     * @param mixed $table
     */
    public function truncate($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }
        return new Truncate($table ?? $this->table);
    }

    /**
     * prepareStatementForSqlObject
     *
     * @return void
     */
    public function prepareStatementForSqlObject(
        \Laminas\Db\Sql\PreparableSqlInterface $sqlObject,
        StatementInterface $statement = null,
        AdapterInterface $adapter = null)
    {
        $this->replaceReferencePathsWithAliases($sqlObject);

        return parent::prepareStatementForSqlObject($sqlObject, $statement, $adapter);
    }

    /**
     * @param SqlInterface     $sqlObject
     * @param AdapterInterface $adapter
     *
     * @return string
     *
     * @throws Exception\InvalidArgumentException
     */
    public function buildSqlString(SqlInterface $sqlObject, AdapterInterface $adapter = null)
    {
        $this->replaceReferencePathsWithAliases($sqlObject);
        return parent::buildSqlString($sqlObject, $adapter);
    }

    /**
     * replaceReferencePathsWithAliases
     *
     * @param  mixed $_target
     *
     * @return void
     */
    public function replaceReferencePathsWithAliases($_target)
    {
        switch (true) {
            case $_target instanceof Select:
                $this->prepareSelect($_target);
                break;
            case $_target instanceof Delete:
                $this->prepareDelete($_target);
                break;
            case $_target instanceof Update:
                $this->prepareUpdate($_target);
                break;
        }
    }

    /**
     * Prepare select sql states
     *
     * @param  $_target
     *
     * @return void
     */
    protected function prepareSelect($_target): void
    {
        $referencePaths = $this->getGateway()->getProcessorsReplacements();

        $rawState = $_target->getRawState();
        foreach ($rawState as $key => $value) {
            switch (true) {
                case $key === Select::COLUMNS:
                    $_target->reset($key)->columns($this->prepareColumns($referencePaths, $value));
                    break;
                case $key === Select::GROUP && ! is_null($value):
                    $_target->reset($key)->group($this->prepareGroup($referencePaths, $value));
                    break;
                case $key === Select::ORDER && ! is_null($value):
                    $_target->reset($key)->order($this->prepareOrder($referencePaths, $value));
                    break;
                case $key === Select::WHERE && ! is_null($value):
                    $this->prepareWhere($referencePaths, $value->getPredicates());
                    break;
            }
        }
    }

    /**
     * Prepare delete sql states
     *
     * @param  $_target
     *
     * @return void
     */
    protected function prepareDelete($_target): void
    {
        $referencePaths = $this->getGateway()->getProcessorsReplacements(false);
        $rawState = $_target->getRawState();

        foreach ($rawState as $key => $value) {
            switch (true) {
                case $key === Delete::SPECIFICATION_WHERE && ! is_null($value):
                    $this->prepareWhere($referencePaths, $value->getPredicates());
                    break;
            }
        }
    }

    /**
     * Prepare update sql states
     *
     * @param  $_target
     *
     * @return void
     */
    protected function prepareUpdate($_target): void
    {
        $referencePaths = $this->getGateway()->getProcessorsReplacements(false);
        $rawState = $_target->getRawState();

        foreach ($rawState as $key => $value) {
            switch (true) {
                case $key === Update::SPECIFICATION_WHERE && ! is_null($value):
                    $this->prepareWhere($referencePaths, $value->getPredicates());
                    break;
            }
        }
    }

    /**
     * prepareOrderOrGroup
     *
     * @param  mixed $_referencePaths
     * @param  mixed $_order
     *
     * @return void
     */
    protected function prepareOrder($_referencePaths, $_order)
    {
        if (is_array($_order) && count($_order) !== 0) {
            foreach($_order as $k => $v) {
                if ($v instanceof ExpressionInterface) {
                    $expression = $v->getExpression();
                    $v->setExpression($this->prepareString($_referencePaths, $expression));
                    continue;
                }
                if (is_int($k)) {
                    $_order[$k] = $this->prepareString($_referencePaths, $_order[$k]);
                } elseif (is_string($k)) {
                    $key = $this->prepareString($_referencePaths, $key);
                    $_order[$key] = $v;
                    unset($_order[$k]);
                }
            }
        }
        return $_order;
    }

    /**
     * prepareGroup
     *
     * @param  mixed $_referencePaths
     * @param  mixed $_group
     *
     * @return void
     */
    protected function prepareGroup($_referencePaths, $_group)
    {
        if (is_array($_group) && count($_group) !== 0) {
            foreach ($_group as $k => $column) {
                $_group[$k] = $this->prepareColumnValue($_referencePaths, $column);
            }
        }

        return $_group;
    }

    /**
     * prepareWhere
     *
     * @param  mixed $_referencePaths
     * @param  mixed $_predicates
     * @param  mixed $_where
     *
     * @return void
     */
    protected function prepareWhere($_referencePaths, $_predicates)
    {
        foreach ($_predicates as $predicate) {
            # - Recursive prepare

            if ($predicate[1] instanceof Predicate\PredicateSet) {
                $this->prepareWhere($_referencePaths, $predicate[1]->getPredicates());
            }

            # - Prepare Operator
            if (method_exists($predicate[1], 'setLeft')) {
                $predicate[1]->setLeft(
                    str_replace(
                        array_keys($_referencePaths),
                        array_values($_referencePaths),
                        $predicate[1]->getLeft()
                    )
                );
            # - Prepare other predicates
            } elseif(method_exists($predicate[1], 'setIdentifier')) {
                $predicate[1]->setIdentifier(
                    str_replace(
                        array_keys($_referencePaths),
                        array_values($_referencePaths),
                        $predicate[1]->getIdentifier()
                    )
                );
            } elseif(method_exists($predicate[1], 'setExpression')) {
                $predicate[1]->setExpression(
                    str_replace(
                        array_keys($_referencePaths),
                        array_values($_referencePaths),
                        $predicate[1]->getExpression()
                    )
                );
            }
        }
    }

    /**
     * prepareColumns
     *
     * @param  mixed $_referencePaths
     * @param  mixed $_columns
     *
     * @return void
     */
    protected function prepareColumns($_referencePaths, $_columns)
    {
        $columns = [];
        if (is_array($_columns) && count($_columns) !== 0) {
            foreach ($_columns as $key => $column) {
                $columns[$this->prepareString($_referencePaths, $key)] = $this->prepareColumnValue($_referencePaths, $column);
            }
        }
        return $columns;
    }

    /**
     * prepareColumnValue
     *
     * @param  mixed $_referencePaths
     * @param  mixed $_column
     *
     * @return void
     */
    protected function prepareColumnValue($_referencePaths, $_column)
    {
        if ($_column instanceof ExpressionInterface) {
            $expression = $_column->getExpression();
            $_column->setExpression($this->prepareString($_referencePaths, $expression));

            $oldParameters = $_column->getParameters();
            if ($oldParameters) {
                $newParameters = [];
                foreach ($oldParameters as $index => $argument) {
                    $type = current($argument);
                    $parameter = key($argument);
                    if ($type === ExpressionInterface::TYPE_IDENTIFIER) {
                        $parameter = $this->prepareString($_referencePaths, $parameter);
                    }
                    $newParameters[$index] = [ $parameter => $type ];
                }
                $_column->setParameters($newParameters);
            }
        } elseif (is_string($_column)) {
            $_column = $this->prepareString($_referencePaths, $_column);
        }

        return $_column;
    }

    /**
     * prepareString
     *
     * @param  mixed $_referencePaths
     * @param  mixed $_string
     *
     * @return void
     */
    protected function prepareString($_referencePaths, $_string)
    {
        return str_replace(array_keys($_referencePaths), array_values($_referencePaths), (string)$_string);
    }

    /**
     * setGateway
     *
     * @param  mixed $_gateway
     */
    public function setGateway($_gateway)
    {
        $this->gateway = $_gateway;
        return $this;
    }

    /**
     * getGateway
     *
     * @return Gateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

}
