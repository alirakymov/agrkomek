<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\GuideCategory;

use Qore\App\SynapseNodes\Components\LanguageTrait;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: GuideCategory
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class GuideCategory extends SynapseBaseEntity
{
    use LanguageTrait;

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
