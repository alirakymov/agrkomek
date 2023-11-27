<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Settings;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity;

/**
 * Class: Settings
 *
 * @see Entity\SynapseBaseEntity
 */
class Settings extends Entity\SynapseBaseEntity
{
    /**
     * search settings by path
     *
     * @param string $_param
     * @param mixed $_default (optional)
     *
     * @return mixed
     */
    public function getParam(string $_param, $_default = null)
    {
        $config = $this['value'];
        $_param = explode('.', $_param);

        foreach ($_param as $paramKey) {
            if (isset($config[$paramKey])) {
                $config = $config[$paramKey];
            } else {
                return $_default;
            }
        }

        return $config;
    }

    /**
     * set param settings by path
     *
     * @param string $_param
     * @param mixed $_value
     *
     * @return mixed
     */
    public function setParam(string $_param, $_value)
    {
        $value = $this['value'];
        $target = &$value;
        $_param = explode('.', $_param);

        foreach ($_param as $paramKey) {
            if (! isset($target[$paramKey])) {
                $target[$paramKey] = [];
            }

            $target = &$target[$paramKey];
        }

        $target = $_value;
        $this['value'] = $value;
        return $this;
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
            if (isset($entity['value'])) {
                $entity['value'] = is_string($entity['value'])
                    ? json_decode($entity['value'], true)
                    : $entity['value'];
            } else {
                $entity['value'] = [];
            }
        });

        static::before('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['value'])) {
                $entity['value'] = is_array($entity['value'])
                    ? json_encode($entity['value'])
                    : $entity['value'];
            } else {
                $entity['value'] = json_encode([]);
            }
        });

        static::after('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['value'])) {
                $entity['value'] = is_string($entity['value'])
                    ? json_decode($entity['value'], true)
                    : $entity['value'];
            }
        });

        parent::subscribe();
    }

}
