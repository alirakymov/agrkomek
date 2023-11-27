<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

use ArrayIterator;
use ArrayObject;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;
use Qore\Collection\Collection;
use Qore\Collection\CollectionInterface;
use Qore\Form\FormManagerInterface;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\SynapseManager;

class Model extends ArrayObject implements ModelInterface
{
    /** @var string - chain node storage cursor */
    const STATE_CURSOR = 'state-storage';

    /** @var string - chain purpose index */
    const CHAIN_PURPOSE = 'chain-purpose';

    /** @var string - chain purpose value for build action */
    const CHAIN_PURPOSE_BUILD = 'build';

    /**
     * @var CollectionInterface;
     */
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
    public function __invoke($_index) : ModelInterface
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
     * @inheritdoc
     */
    public function isBuild(bool $_build = null)
    {
        if (is_null($_build)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_BUILD;
        }

        $this[$this::CHAIN_PURPOSE] = $_build ? $this::CHAIN_PURPOSE_BUILD : null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setForm(FormManagerInterface $_form): ModelInterface
    {
        $this[FormManagerInterface::class] = $_form;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForm(): ?FormManagerInterface
    {
        return $this[FormManagerInterface::class] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setSynapseManager(SynapseManager $_sm): ModelInterface
    {
        $this[SynapseManager::class] = $_sm;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSynapseManager(): SynapseManager
    {
        return $this[SynapseManager::class];
    }

    /**
     * @inheritdoc
     */
    public function setFilters(CollectionInterface $_filters): ModelInterface
    {
        $this['filters'] = $_filters;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentFilters(): array 
    {
        $path = explode('.', $this['path']);
        array_shift($path);

        /** @var CollectionInterface */
        $filters = $this['filters'];
        $filters = $filters
            ->filter(function($_filter) use ($path) {
                $filterPath = explode('.', $_filter['referencePath']);
                array_shift($filterPath);
                return $filterPath == $path;
            })
            ->map(fn ($_filter) => $_filter['filters'])
            ->reduce(fn($_result, $_item) => array_merge($_result, $_item), []);

        return $filters;
    }

    /**
     * @inheritdoc
     */
    public function setRequest(ServerRequestInterface $_request): ModelInterface
    {
        $this[ServerRequestInterface::class] = $_request;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this[ServerRequestInterface::class];
    }

}
