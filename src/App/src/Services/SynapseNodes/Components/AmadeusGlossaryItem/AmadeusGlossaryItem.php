<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusGlossaryItem;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: AmadeusGlossaryItem
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class AmadeusGlossaryItem extends SynapseBaseEntity
{
    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            if(! isset($entity->data) && ! is_string($entity->data)) {
                $entity->data = json_encode($entity->data, JSON_UNESCAPED_UNICODE);
                $entity->idGlossary = intval($entity->glossary()->id);
            }
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
