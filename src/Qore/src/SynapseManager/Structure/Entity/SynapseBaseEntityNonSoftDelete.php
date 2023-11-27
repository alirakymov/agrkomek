<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity\Entity;
use Qore\ORM\Entity\SoftDeleteInterface;

/**
 * Class: SynapseBaseEntity
 *
 * @see Entity\Entity
 */
class SynapseBaseEntityNonSoftDelete extends Entity
{
    /**
     * Set option
     *
     * @param string $_option  - option name
     * @param  $_value 
     *
     * @return SynapseBaseEntity
     */
    public function setOption(string $_option, $_value): SynapseBaseEntity
    {
        $options = is_string($this['__options']) ? json_decode($this['__options'], true) : ($this['__options'] ?? []);
        $options = array_merge($options, [ $_option => $_value, ]);

        $this['__options'] = is_string($this['__options']) 
            ? json_encode($options, JSON_UNESCAPED_UNICODE) 
            : $options;

        return $this;
    }

    /**
     * Get option value
     *
     * @param string $_option 
     * @param  $_default (optional) 
     *
     * @return mixed
     */
    public function getOption(string $_option, $_default = null)
    {
        if (is_null($this['__options'])) {
            return $_default;
        }

        $options = is_string($this['__options']) ? json_decode($this['__options'], true) : $this['__options'];
        return $options[$_option] ?? $_default;
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
