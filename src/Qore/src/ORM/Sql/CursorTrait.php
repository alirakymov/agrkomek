<?php

namespace Qore\ORM\Sql;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\ExpressionInterface;
use Laminas\Db\Sql\Predicate\Operator;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Qore\ORM\Gateway\Gateway;

trait CursorTrait
{
    /**
     * @var \Qore\ORM\Gateway\Gateway|null
     */
    private ?Gateway $gateway = null;

    /**
     * Set gateway
     *
     * @param \Qore\ORM\Gateway\Gateway $_gateway
     *
     * @return void
     */
    public function setGateway(Gateway $_gateway): void
    {
        $this->gateway = $_gateway;
    }

    /**
     * Handle cursors replacements
     *
     * @return void
     */
    public function handleCursors(): void
    {
        switch (true) {
            case $this instanceof Select:
                $this->handleColumnsCursors();
                $this->handleGroupCursors();
                $this->handleOrderCursors();
                break;
            case $this instanceof Where:
                $this->handlePredicateCursors();
                break;
        }
    }

    /**
     * Handle columns cursors
     *
     * @return void
     */
    public function handleColumnsCursors(): void
    {
        $columns = [];
        foreach ($this->columns as $key => $column) {
            $columns[$this->handleColumnCursor($key)] = $this->handleColumnCursor($column);
        }
        $this->columns = $columns;
    }

    /**
     * Handle group cursors
     *
     * @return void
     */
    public function handleGroupCursors(): void
    {
        if ($this->group === null) {
            return;
        }

        foreach ($this->group as &$column) {
            $column = $this->handleColumnCursor($column);
        }
    }

    /**
     * Handle order cursors
     *
     * @return void
     */
    public function handleOrderCursors(): void
    {
        if (empty($this->order)) {
            return;
        }

        foreach ($this->order as $k => $v) {
            if (! is_int($k)) {
                $this->order[$this->handleColumnCursor($k)] = $v;
                unset($this->order[$k]);
            } else {
                $this->order[$k] = $this->handleColumnCursor($v);
            }
        }
    }

    /**
     * Handle where cursors
     *
     * @return void
     */
    public function handleWhereCursors(): void
    {
        $this->where->handlePredicateCursors();
    }

    /**
     * Handle predicate cursors
     *
     * @param array $_predicates (optional)
     *
     * @return void
     */
    public function handlePredicateCursors(array $_predicates = null): void
    {
        $replacements = $this->getReplacements();

        if (is_null($_predicates)) {
            $_predicates = $this->getPredicates();
        }

        foreach ($_predicates as $predicate) {
            # - Recursive prepare
            if ($predicate[1] instanceof PredicateSet) {
                $this->handlePredicateCursors($predicate[1]->getPredicates());
            }
            # - Prepare Expression
            if ($predicate[1] instanceof Operator) {
                $predicate[1]->setLeft(
                    str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $predicate[1]->getLeft()
                    )
                );
            } elseif(method_exists($predicate[1], 'setIdentifier')) {
                $predicate[1]->setIdentifier(
                    str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $predicate[1]->getIdentifier()
                    )
                );
            } elseif(method_exists($predicate[1], 'setExpression')) {
                $predicate[1]->setExpression(
                    str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $predicate[1]->getExpression()
                    )
                );
            }
        }
    }

    /**
     * Handle column cursor
     *
     * @param Expression|string $_column
     *
     * @return Expression|string
     */
    protected function handleColumnCursor($_column)
    {
        $replacements = $this->getReplacements();

        if ($_column instanceof ExpressionInterface) {
            $_column->setExpression(
                str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $_column->getExpression()
                )
            );

            $oldParameters = $_column->getParameters();
            if ($oldParameters) {
                $newParameters = [];
                foreach ($oldParameters as $index => $argument) {
                    $type = current($argument);
                    $parameter = key($argument);
                    if ($type === ExpressionInterface::TYPE_IDENTIFIER) {
                        $parameter = str_replace(
                            array_keys($replacements),
                            array_values($replacements),
                            $parameter
                        );
                    }
                    $newParameters[$index] = [ $parameter => $type ];
                }
                $_column->setParameters($newParameters);
            }

        } elseif (is_string($_column)) {
            $_column = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $_column
            );
        }

        return $_column;
    }

    /**
     * Get replacements
     *
     * @return array
     */
    protected function getReplacements(): array
    {
        return [
            '@this' => $this->gateway->getProcessor()->getProcessorPath(),
        ];
    }

}
