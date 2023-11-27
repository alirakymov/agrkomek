<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Report;

use ArrayIterator;
use ArrayObject;
use Qore\Collection\Collection;
use Qore\Collection\CollectionInterface;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseService;

class Model extends ArrayObject implements ModelInterface
{
    const MAPPING_STATE = 'mapping-state';

    const PREPARE_STATE = 'prepare-state';

    const MAPPING_CURSOR = 'mapping-cursor';

    const OBJECTS_COLLECTION = 'objects-collection';

    const CHAIN_PURPOSE = 'chain-purpose';

    const CHAIN_PURPOSE_MAPPING = 'mapping';

    const CHAIN_PURPOSE_PREPAREOBJECTS = 'prepare-objects';

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
     * @param mixed $_index
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
     * Merge
     *
     * @param  $_object 
     *
     * @return ModelInterface 
     */
    public function merge($_object): ModelInterface
    {
        foreach ($_object as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getServiceCollection() : CollectionInterface
    {
        $this->serviceCollection ??= new Collection([]);
        return $this->serviceCollection;
    }

    /**
     * @inheritdoc
     */
    public function setServiceCollection(CollectionInterface $_serviceCollection) : void
    {
        $this->serviceCollection = $_serviceCollection;
    }

    /**
     * @inheritdoc
     */
    public function getLastService() : SynapseService
    {
        return $this->serviceCollection->last();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function isPrepare(bool $_objects = null)
    {
        if (is_null($_objects)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_PREPAREOBJECTS;
        }

        $this[$this::CHAIN_PURPOSE] = $_objects ? $this::CHAIN_PURPOSE_PREPAREOBJECTS : null;
        return $this;
    }

}
