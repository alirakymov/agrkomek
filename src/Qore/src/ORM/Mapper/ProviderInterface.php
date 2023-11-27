<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper;

use Qore\ORM;

interface ProviderInterface
{
    /**
     * get
     *
     * @param string $_entity
     */
    public function get(string $_entity) : Mapper;

    /**
     * initialize
     *
     * @param ORM\ModelManager $_mm
     */
    public function initialize(ORM\ModelManager $_mm) : void;
}
