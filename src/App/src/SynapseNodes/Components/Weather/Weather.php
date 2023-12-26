<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Weather;

use Qore\App\SynapseNodes\Components\LanguageTrait;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: ArticleType
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Weather extends SynapseBaseEntity
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
