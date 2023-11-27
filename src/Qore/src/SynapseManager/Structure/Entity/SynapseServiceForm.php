<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity;

/**
 * Class: SynapseServiceForm
 *
 * @see Entity\Entity
 */
class SynapseServiceForm extends Entity\Entity
{
    /**
     * FORM_ENTITY
     */
    const FORM_ENTITY = 0;

    /**
     * FORM_HIDDEN_SELECTION
     */
    const FORM_HIDDEN_SELECTION = 1;

    /**
     * FORM_MULTIPLE_SELECTION
     */
    const FORM_MULTIPLE_SELECTION = 2;

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
            } else {
                $entity['__options'] = [];
            }
        });

        static::before('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['__options'])) {
                $entity['__options'] = is_array($entity['__options'])
                    ? json_encode($entity['__options'])
                    : $entity['__options'];
            } else {
                $entity['__options'] = json_encode([]);
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
    }

}
