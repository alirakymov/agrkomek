<?php

namespace Qore\SynapseManager\Plugin\Operation;

interface StorageInterface
{
    /**
     * Get UUID of launched operation
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get data from entity storage
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Set data to entity storage as array
     *
     * @param array $_data
     *
     * @return StorageInterface
     */
    public function setData(array $_data): StorageInterface;

}
