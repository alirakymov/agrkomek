<?php

declare(strict_types=1);

namespace Qore\Collection;

use Cake\Collection\CollectionInterface;
use Cake\Collection\Iterator\SortIterator;
use Qore\ORM\Entity\EntityInterface;

/**
 * Trait: CollectionTrait
 */
trait CollectionTrait
{
    /**
     * newCollection
     *
     * @param ... $args
     * @param mixed $args
     */
    protected function newCollection(...$args) : CollectionInterface
    {
        return new Collection(...$args);
    }

    /**
     * implode
     *
     * @param string $_separator
     */
    public function implode(string $_separator = '') : string
    {
        return implode($_separator, $this->toArray());
    }

    /**
     * apply all transform iterations (unwrap generators)
     *
     */
    public function apply()
    {
        return $this->compile();
    }

    /**
     * getOne
     *
     * @param int $_from
     */
    public function getOne(int $_from)
    {
        return $this->get($_from)->first();
    }

    // /**
    //  * {@inheritDoc}
    //  */
    // public function sortBy($callback, $dir = \SORT_DESC, $type = \SORT_NUMERIC)
    // {
    //     return (new SortIterator($this->unwrap(), $callback, $dir, $type))->compile();
    // }

    /**
     * get
     *
     * @param mixed $_from
     * @param int $_size
     */
    public function get($_from, $_size = 1)
    {
        return $this->take($_size, $_from);
    }

    /**
     * toDump
     *
     */
    public function dump()
    {
        $result = $this->map(function($_element){
            if ($_element instanceof EntityInterface) {
                return $_element->dump();
            } elseif ($_element instanceof Collection) {
                return $_element->dump();
            } else {
                return $_element;
            }
        });

        return $result->toList();
    }

}
