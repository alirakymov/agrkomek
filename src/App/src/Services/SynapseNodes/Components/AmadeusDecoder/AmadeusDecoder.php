<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusDecoder;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: AmadeusDecoder
 *
 * @see SynapseBaseEntity
 */
class AmadeusDecoder extends SynapseBaseEntity
{
    /**
     * Get regex
     *
     * @return string|null
     */
    public function getRegex(): ?string
    {
        if (! $this->regex) {
            return null;
        }

        return in_array(mb_substr($this->regex, 0, 1), ['/', '#', '~', '%', '@', ';', '`'])
            ? $this->regex
            : sprintf('/%s/u', $this->regex);
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
