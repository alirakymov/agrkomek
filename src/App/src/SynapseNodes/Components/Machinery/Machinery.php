<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Machinery;


use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Machinery
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Machinery extends SynapseBaseEntity
{
    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();

        static::before('save', function($_event) {
            $entity = $_event->getTarget();

            $entity->params = is_string($entity->params) 
                ? $entity->params 
                : json_encode($entity->params, JSON_UNESCAPED_UNICODE);

            $entity->images = is_string($entity->images) 
                ? $entity->images 
                : json_encode($entity->images, JSON_UNESCAPED_UNICODE);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();

            $entity->params = $entity->params ?: [];
            $entity->images = $entity->images ?: [];

            $entity->params = is_string($entity->params) 
                ? json_decode($entity->params, true) 
                : $entity->params;

            $entity->images = is_string($entity->images) 
                ? json_decode($entity->images, true) 
                : $entity->images;
        });

        static::after('initialize', $func);

    }

}
