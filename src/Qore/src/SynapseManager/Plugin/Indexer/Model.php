<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use ArrayIterator;
use ArrayObject;
use DateTime;
use Qore\Collection\Collection;
use Qore\Collection\CollectionInterface;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Model extends ArrayObject implements ModelInterface
{
    const MAPPING_STATE = 'mapping-state';

    const MAPPING_CURSOR = 'mapping-cursor';

    const OBJECTS_COLLECTION = 'objects-collection';

    const FILTERS_COLLECTION = 'filters-collection';

    const CHAIN_PURPOSE = 'chain-purpose';

    const CHAIN_PURPOSE_MAPPING = 'mapping';

    const CHAIN_PURPOSE_INDEXING = 'indexing';

    const CHAIN_PURPOSE_SEARCH = 'search';

    private CollectionInterface $serviceCollection;

    /**
     * Constructor
     *
     * @param  $array (optional)
     * @param int $flags (optional)
     * @param string $iteratorClass (optional)
     */
    public function __construct($array = [], int $flags = 0, string $iteratorClass = ArrayIterator::class)
    {
        parent::__construct($array, $flags, $iteratorClass);
    }

    /**
     * __invoke
     *
     * @param mixed $_target
     */
    public function __invoke($_index) : Model
    {
        return $this->getState($_index);
    }

    /**
     * Set objects
     *
     * @param \Qore\Collection\CollectionInterface $_objects
     *
     * @return Model
     */
    public function setObjects(CollectionInterface $_objects) : ModelInterface
    {
        $this['objects'] = $_objects;
        return $this;
    }

    /**
     * Generate new Or return exists state object
     *
     * @param string $_index
     *
     * @return Model
     */
    public function getState(string $_index) : ModelInterface
    {
        $this[$_index] ??= new static();
        return $this[$_index];
    }

    /**
     * Return synapse service chain
     *
     * @return \Qore\Collection\CollectionInterface
     */
    public function getServiceCollection() : CollectionInterface
    {
        $this->serviceCollection ??= new Collection([]);
        return $this->serviceCollection;
    }

    /**
     * Set synapse service chain
     *
     * @param \Qore\Collection\CollectionInterface $_serviceCollection
     *
     * @return void
     */
    public function setServiceCollection(CollectionInterface $_serviceCollection) : void
    {
        $this->serviceCollection = $_serviceCollection;
    }

    /**
     * Get last service
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseService
     */
    public function getLastService() : SynapseService
    {
        return $this->serviceCollection->last();
    }

    /**
     * Set/Check for chain purpose
     *
     * @param bool $_mapping (optional)
     *
     * @return bool|Model
     */
    public function isMapping(bool $_mapping = null)
    {
        if (is_null($_mapping)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_MAPPING;
        }

        $this[$this::CHAIN_PURPOSE] = $_mapping ? $this::CHAIN_PURPOSE_MAPPING : null;
        return $this;
    }

    /**
     * Set/Check chain purpose
     *
     * @param bool $_indexing (optional)
     *
     * @return bool|Model
     */
    public function isIndexing(bool $_indexing = null)
    {
        if (is_null($_indexing)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_INDEXING;
        }

        $this[$this::CHAIN_PURPOSE] = $_indexing ? $this::CHAIN_PURPOSE_INDEXING : null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFilters(CollectionInterface $_filters): ModelInterface
    {
        $this['filters'] = Qore::collection($_filters->map(function($_filter) {
            $path = explode('.', $_filter['referencePath']);
            array_shift($path);
            return new Filter(
                implode('.', $path), 
                $_filter['filters'], 
                isset($_filter['subject']) && is_object($_filter['subject']) && $_filter['subject'] instanceof SynapseServiceSubject ? $_filter['subject'] : null
            );
        })->toList());


        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isSearch(bool $_search = null)
    {
        if (is_null($_search)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_SEARCH;
        }

        $this[$this::CHAIN_PURPOSE] = $_search ? $this::CHAIN_PURPOSE_SEARCH : null;
        return $this;
    }

}
