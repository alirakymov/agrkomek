<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\ConsultancyMessage;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Ramsey\Uuid\Uuid;

/**
 * Class: Article
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class ConsultancyMessage extends SynapseBaseEntity
{
    const DIRECTION_IN = 1;
    const DIRECTION_OUT = 2;

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
