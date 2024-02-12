<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\OperationPhase;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: OperationPhase
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class OperationPhase extends SynapseBaseEntity
{
    /**
     * Generate hash for script file name
     *
     * @return string
     */
    public function getIdentifierHash(): string
    {
        return sprintf('phase_%s', sha1($this['id']));
    }

    /**
     * Compare saved hash with hash of script
     *
     * @return string 
     */
    public function isModified(): bool
    {
        return ($this['hash'] ?? null) !== sha1($this->script);
    }

    /**
     * Update hash
     *
     * @return OperationPhase
     */
    public function udpateHash(): OperationPhase
    {
        $this['hash'] = sha1($this->script);
        return $this;
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
