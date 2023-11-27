<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity\Entity;
use Qore\ORM\Entity\SoftDeleteInterface;

/**
 * Class: SynapseBaseEntity
 *
 * @see Entity\Entity
 */
class SynapseBaseEntity extends SynapseBaseEntityNonSoftDelete implements SoftDeleteInterface
{
}
