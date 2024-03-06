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
    }

}
