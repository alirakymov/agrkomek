<?php

declare(strict_types=1);

namespace Qore\ORM\Sql;

use Qore\ORM\Gateway;
use Laminas\Db\Sql;
use Laminas\Db\Sql\Delete as SqlDelete;
use Laminas\Db\Sql\Predicate;
use Laminas\Db\Sql\ExpressionInterface;

class Delete extends SqlDelete
{
    /**
     * gateway
     *
     * @var mixed
     */
    protected $gateway = null;

    /**
     * setGateway
     *
     * @param Gateway\Gateway $_gateway
     */
    public function setGateway(Gateway\Gateway $_gateway)
    {
        $this->gateway = $_gateway;
    }

    /**
     * handleCursors
     *
     */
    public function handleCursors()
    {
        $this->handleOrderCursors();
        $this->handleWhereCursors();
    }

    /**
     * handleOrderCursors
     *
     */
    public function handleOrderCursors()
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
     * handleWhereCursors
     *
     * @param array $_predicates
     */
    public function handleWhereCursors(array $_predicates = null)
    {
        $replacements = $this->getReplacements();

        if (is_null($_predicates)) {
            $_predicates = $this->where->getPredicates();
        }

        foreach ($_predicates as $predicate) {
            # - Recursive prepare
            if ($predicate[1] instanceof Predicate\PredicateSet) {
                $this->handleWhereCursors($predicate[1]->getPredicates());
            }
            # - Prepare Expression
            if ($predicate[1] instanceof Predicate\Operator) {
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
     * getReplacements
     *
     */
    protected function getReplacements()
    {
        return [
            '@this' => $this->gateway->getProcessor()->getProcessorPath(),
        ];
    }

}

