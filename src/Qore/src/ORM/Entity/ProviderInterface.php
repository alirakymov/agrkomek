<?php

declare(strict_types=1);

namespace Qore\ORM\Entity;

use Qore\ORM;

interface ProviderInterface
{
    /**
     * initialize
     *
     * @param ORM\ModelManager $_mm
     */
    public function initialize(ORM\ModelManager $_mm) : void;
}
