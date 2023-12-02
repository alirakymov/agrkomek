<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\ConsultancySession;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Ramsey\Uuid\Uuid;

/**
 * Class: Article
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class ConsultancySession extends SynapseBaseEntity
{
    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::after('initialize', $func = function($_event) {
            $entity = $_event->getTarget();
            if (! $entity->token) {
                $entity->token = (string)Uuid::uuid7();
            }
        });

        parent::subscribe();
    }

}
