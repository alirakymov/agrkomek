<?php

declare(strict_types=1);

namespace Qore\ORM\Sql;

use Qore\ORM\Gateway;
use Laminas\Db\Sql\Predicate;
use Laminas\Db\Sql\Where as ZendWhere;

class Where extends ZendWhere
{
    use CursorTrait;
    /**
     * __invoke
     *
     * @param mixed $_predicates
     * @param mixed $_combination
     */
    public function __invoke($_predicates, $_combination = self::OP_AND) : Where
    {
        $predicateSet = new static();
        $predicateSet->addPredicates($_predicates, $_combination);
        $this->addPredicate($predicateSet, ($this->nextPredicateCombineOperator) ?: $this->defaultCombination);
        return $this;
    }

    /**
     * AND
     *
     * @param mixed $_predicates
     */
    public function AND($_predicates, $_combination = self::OP_AND) : Where
    {
        $predicateSet = new static();

        if (is_callable($_predicates)) {
            $_predicates($predicateSet, $_combination);
        } else {
            $predicateSet->addPredicates($_predicates, $_combination);
        }

        $this->addPredicate($predicateSet, self::OP_AND);
        return $this;
    }

    /**
     * OR
     *
     * @param mixed $_predicates
     */
    public function OR($_predicates, $_combination = self::OP_AND) : Where
    {
        $predicateSet = new static();

        if (is_callable($_predicates)) {
            $_predicates($predicateSet, $_combination);
        } else {
            $predicateSet->addPredicates($_predicates, $_combination);
        }

        $this->addPredicate($predicateSet, self::OP_OR);
        return $this;
    }

    /**
     * operator
     *
     */
    public function operator($_left, $_operator, $_right) : Predicate\Operator
    {
        return new Predicate\Operator($_left, $_operator, $_right);
    }

    /**
     * setPredicates
     *
     * @param array $_predicates
     */
    public function setPredicates(array $_predicates)
    {
        $this->predicates = $_predicates;
        return $this;
    }

}
