<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper;

use Qore\Database\Adapter\Adapter;

interface MapperInterface
{
    /**
     * initTables
     *
     */
    public function initTables() : void;

    /**
     * initReferences
     *
     */
    public function initReferences() : void;

    /**
     * setStructure
     *
     */
    public function setStructure(array $_structure) : void;

    /**
     * getStructure
     *
     */
    public function getStructure() : array;

    /**
     * getTable
     *
     */
    public function getTable(string $_entity) : ?Table\Table;
}
