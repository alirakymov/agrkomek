<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity;
use function PHPSandbox\wrap;

/**
 * Class: SynapseService
 *
 * @see Entity\Entity
 */
class SynapseService extends Entity\Entity
{
    /**
     * Check for data index
     *
     * @return bool
     */
    public function isIndexed(): bool
    {
        return (int)$this->index === 1;
    }

    /**
     * Get synapse name
     *
     * @return string
     */
    public function getSynapseServiceName(): string
    {
        return sprintf('%s:%s', $this->synapse()->name, $this->name);
    }

}
