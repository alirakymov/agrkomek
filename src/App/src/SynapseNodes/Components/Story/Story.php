<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Story;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Story
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Story extends SynapseBaseEntity
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

            $entity->images = is_string($entity->images) 
                ? $entity->images 
                : json_encode($entity->images, JSON_UNESCAPED_UNICODE);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();

            $entity->images = $entity->images ?: [];

            $entity->images = is_string($entity->images) 
                ? json_decode($entity->images, true) 
                : $entity->images;
        });

        static::after('initialize', $func);
    }

}
