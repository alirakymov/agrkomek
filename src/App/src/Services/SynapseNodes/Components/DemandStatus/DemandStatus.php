<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\DemandStatus;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: DemandStatus
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class DemandStatus extends SynapseBaseEntity
{
    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
