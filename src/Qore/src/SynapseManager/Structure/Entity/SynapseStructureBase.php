<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity\Entity;

/**
 * Class: SynapseBaseEntity
 *
 * @see Entity\Entity
 */
class SynapseStructureBase extends Entity
{
    /**
     * @inheritdoc
     */
    public function offsetGet($_key): mixed
    {
        return parent::offsetGet($_key);
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('initialize', function($_event){
            $params = $_event->getParams();
            $entity = $params['entityData'];
            if (isset($entity['__options'])) {
                $entity['__options'] = is_string($entity['__options'])
                    ? json_decode($entity['__options'], true)
                    : $entity['__options'];
            }
        });

        static::before('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['__options'])) {
                $entity['__options'] = is_array($entity['__options'])
                    ? json_encode($entity['__options'])
                    : $entity['__options'];
            }
        });

        static::after('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['__options'])) {
                $entity['__options'] = is_string($entity['__options'])
                    ? json_decode($entity['__options'], true)
                    : $entity['__options'];
            }
        });

        parent::subscribe();
    }
}
