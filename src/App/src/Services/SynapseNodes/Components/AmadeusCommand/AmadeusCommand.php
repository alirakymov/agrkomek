<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusCommand;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: AmadeusCommand
 *
 * @see SynapseBaseEntity
 */
class AmadeusCommand extends SynapseBaseEntity
{
    /**
     * subscribe
     *
     */
    public static function subscribe()
    {

        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            $entity->data = json_encode($entity->data);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();
            $entity->data = is_string($entity->data) 
                ? json_decode($entity->data, true) 
                : $entity->data;
        });

        static::after('initialize', $func);
        
        parent::subscribe();
    }

}
