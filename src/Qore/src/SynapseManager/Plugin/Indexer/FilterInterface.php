<?php

namespace Qore\SynapseManager\Plugin\Indexer;

interface FilterInterface
{
    /**
     * Return filtering subject path
     *
     * @return string 
     */
    public function getPath(): string;

    /**
     * Prepare filter for search engine
     *
     * @return array 
     */
    public function prepareFilters(): array;

}
