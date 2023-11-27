<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

interface ReferenceProcessorInterface
{
    /**
     * getTableAlias
     *
     */
    public function getTableAlias() : string;

    /**
     * getReferences
     *
     */
    public function getTableReferences() : array;
}
