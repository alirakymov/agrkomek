<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Qore\Collection\CollectionInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;

interface ModelInterface
{

    /**
     * Set objects
     *
     * @param \Qore\Collection\CollectionInterface $_objects
     *
     * @return Model
     */
    public function setObjects(CollectionInterface $_objects) : ModelInterface;

    /**
     * Set filters 
     *
     * @param \Qore\Collection\CollectionInterface $_filters
     *
     * @return Model
     */
    public function setFilters(CollectionInterface $_filters) : ModelInterface;

    /**
     * Generate new Or return exists state object
     *
     * @param string $_index
     *
     * @return Model
     */
    public function getState(string $_index) : ModelInterface;

    /**
     * Return synapse service chain
     *
     * @return \Qore\Collection\CollectionInterface
     */
    public function getServiceCollection() : CollectionInterface;

    /**
     * Set synapse service chain
     *
     * @param \Qore\Collection\CollectionInterface $_serviceCollection
     *
     * @return void
     */
    public function setServiceCollection(CollectionInterface $_serviceCollection) : void;

    /**
     * Get last service
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseService
     */
    public function getLastService(): SynapseService;

    /**
     * Set/Check for chain purpose
     *
     * @param bool $_mapping (optional)
     *
     * @return bool|Model
     */
    public function isMapping(bool $_mapping = null);

    /**
     * Set/Check chain purpose
     *
     * @param bool $_indexing (optional)
     *
     * @return bool|Model
     */
    public function isIndexing(bool $_indexing = null);

    /**
     * Set/Check chain purpose
     *
     * @param bool $_search (optional)
     *
     * @return bool|Model
     */
    public function isSearch(bool $_search = null);

}
