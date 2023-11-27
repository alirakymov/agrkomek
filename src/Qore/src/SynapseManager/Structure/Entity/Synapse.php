<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity;

/**
 * Class: Synapse
 *
 * @see Entity\Entity
 */
class Synapse extends SynapseStructureBase
{
    /**
     * Check for tree structure
     *
     * @return bool 
     */
    public function isTree(): bool
    {
        return (int)$this['tree'] == 1;
    }

    /**
     * offsetSet
     *
     * @param mixed $_index
     * @param mixed $_value
     */
    public function offsetSet($_index, $_value) : void
    {
        if ($_index === 'name') {
            $_value = ucfirst($_value);
        }
        parent::offsetSet($_index, $_value);
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_e) {
            $entity = $_e->getTarget();
            $entity->tree = (int)$entity->tree;
        });

        parent::subscribe();
    }

}
