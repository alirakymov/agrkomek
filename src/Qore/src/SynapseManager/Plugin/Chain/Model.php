<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Chain;

use ArrayIterator;
use ArrayObject;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;
use Qore\Collection\Collection;
use Qore\Collection\CollectionInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Model extends ArrayObject implements ModelInterface
{
    /**
     * @var \Qore\Collection\CollectionInterface
     */
    protected CollectionInterface $serviceCollection;

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
     * Generate new Or return exists state object
     *
     * @param string $_index
     *
     * @return Model
     */
    public function getState(string $_index) : Model
    {
        $this[$_index] ??= new static();
        return $this[$_index];
    }

    /**
     * Set chain path
     *
     * @param string $_path (optional) [TODO:description]
     *
     * @return Model
     */
    public function setPath(string $_path = null) : Model
    {
        $this[static::CHAIN_PATH] = $_path;
        return $this;
    }

    /**
     * Get path for current chain point
     *
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this[static::CHAIN_PATH] ?? null;
    }

    /**
     * Set cursor
     *
     * @param ModelInterface $_cursor (optional) [TODO:description]
     *
     * @return Model
     */
    public function setCursor(ModelInterface $_cursor = null) : Model
    {
        $this[static::CHAIN_CURSOR] = $_cursor;
        return $this;
    }

    /**
     * Get cursor
     *
     * @return ModelInterface|null
     */
    public function getCursor() : ?ModelInterface
    {
        return $this[static::CHAIN_CURSOR] ?? null;
    }

    /**
     * Set synapse service subject
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseServiceSubject $_subject (optional)
     *
     * @return Model
     */
    public function setSubject(SynapseServiceSubject $_subject = null): Model
    {
        $this[static::CHAIN_SUBJECT] = $_subject;
        return $this;
    }

    /**
     * Get synapse service subject
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseServiceSubject|null
     */
    public function getSubject(): ?SynapseServiceSubject
    {
        return $this[static::CHAIN_SUBJECT] ?? null;
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
     * @return Model
     */
    public function setServiceCollection(CollectionInterface $_serviceCollection) : Model
    {
        $this->serviceCollection = $_serviceCollection;
        return $this;
    }

    /**
     * Get last service
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseService|null
     */
    public function getLastService() : ?SynapseService
    {
        return $this->serviceCollection->last();
    }

    /**
     * {@inheritDoc}
     */
    public function isRoot(): bool
    {
        return $this->getPath() === Chain::ROOT_SERVICE_POINTER;
    }

}
