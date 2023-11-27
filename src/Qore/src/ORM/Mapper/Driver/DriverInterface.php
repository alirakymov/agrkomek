<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper\Driver;

use Qore\ORM\Mapper;

interface DriverInterface
{
    /**
     * setMapper
     *
     */
    public function setMapper(Mapper\Mapper $_mapper) : void;

    /**
     * prepareTables
     *
     */
    public function prepareTables() : void;

    /**
     * prepareReferences
     *
     */
    public function prepareReferences() : void;
}
