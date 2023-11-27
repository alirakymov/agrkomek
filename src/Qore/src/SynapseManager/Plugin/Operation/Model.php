<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Qore\DealingManager\Model as DealingManagerModel;
use Qore\DealingManager\ScenarioInterface;
use Qore\Lock\Lock;

class Model extends DealingManagerModel implements ModelInterface
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected ServerRequestInterface $_request;

    /**
     * @var \Qore\Lock\Lock
     */
    protected Lock $_lock;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected CacheInterface $_cache;

    /**
     * @var TaskInterface
     */
    protected ?TaskInterface $_task = null;

    /**
     * @var bool
     */
    protected bool $_initialize = false;

    /**
     * @var bool
     */
    protected bool $_process = false;

    /**
     * @var \Qore\DealingManager\ScenarioInterface
     */
    protected ScenarioInterface $_next;

    /**
     * @inheritdoc
     */
    public function setLock(Lock $_lock): ModelInterface
    {
        $this->_lock = $_lock;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCache(CacheInterface $_cache): ModelInterface
    {
        $this->_cache = $_cache;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRequest(ServerRequestInterface $_request): ModelInterface
    {
        $this->_request = $_request;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->_request;
    }

    /**
     * @inheritdoc
     */
    public function setTask(TaskInterface $_task): ModelInterface
    {
        $this->_task = $_task;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTask(): ?TaskInterface
    {
        return $this->_task;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $_identifier): ModelInterface
    {
        $this[static::INDENTIFIER_INDEX] = $_identifier;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this[static::INDENTIFIER_INDEX] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setArtificerName(string $_artificerName): ModelInterface
    {
        $this[static::ARTIFICER_NAME_INDEX] = $_artificerName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getArtificerName(): ?string
    {
        return $this[static::ARTIFICER_NAME_INDEX] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setChain(array $_chain): ModelInterface
    {
        $this[static::CHAIN_INDEX] = $_chain;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChain(): ?array
    {
        return $this[static::CHAIN_INDEX] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setActionRoute(string $_actionRoute): ModelInterface
    {
        $this[static::ACTION_ROUTE_INDEX] = $_actionRoute;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActionRoute(): ?string
    {
        return $this[static::ACTION_ROUTE_INDEX] ?? null;
    }

    /**
     * Generate new or return exists state object by index
     *
     * @param PhaseInterface|string $_index
     *
     * @return StateInterface
     */
    public function __invoke($_index) : StateInterface
    {
        if (is_object($_index) && $_index instanceof PhaseInterface) {
            return $this->getState($_index->getIdentifier());
        }

        if ($_index === AbstractPhase::global) {
            return $this->getState($_index);
        }

        return $this[$_index] ??= new State();
    }

    /**
     * Generate new Or return exists state object
     *
     * @param string|null $_index (optional)
     * @return StateInterface
     */
    public function getState(string $_index = null) : StateInterface
    {
        $this[static::PHASE_STATE_CONTAINER] ??= new State();
        return ! is_null($_index)
            ? $this[static::PHASE_STATE_CONTAINER]($_index)
            : $this[static::PHASE_STATE_CONTAINER];
    }

    /**
     * @inheritdoc
     */
    public function isInitialize(bool $_bool = null)
    {
        if (is_null($_bool)) {
            return $this->_initialize;
        }

        $this->flush();
        $this->_initialize = $_bool;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isProcess(bool $_bool = null)
    {
        if (is_null($_bool)) {
            return $this->_process;
        }

        $this->flush();
        $this->_process = $_bool;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setNext(ScenarioInterface $_next): ModelInterface
    {
        $this->_next = $_next;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function next(PhaseInterface $_phase = null)
    {
        if (! is_null($_phase)) {
            $this->synchronized(function() use ($_phase) {
                $processedPhases = $this($this::PROCESSED_PHASES);
                $processedPhases[] = $_phase->getIdentifier();
            });
        }

        return $this->_next->process($this);
    }

    /**
     * @inheritdoc
     */
    public function isProcessed(PhaseInterface $_phase): bool
    {
        return array_search($_phase->getIdentifier(), (array)$this($this::PROCESSED_PHASES), true) !== false;
    }

    /**
     * @inheritdoc
     */
    public function synchronized(Closure $_closure)
    {
        $identifier = $this->getIdentifier();
        return ($this->_lock)(sprintf('operation-lock.%s', $identifier), function() use ($_closure, $identifier) {
            # - Update current state from storage
            $currentStates = $this[static::PHASE_STATE_CONTAINER];
            $this->_cache->has($identifier)
                && $this->exchangeArray($this->_cache->get($identifier));
            # - Replace state objects for save links from application
            foreach ($currentStates as $index => $state) {
                $state->exchangeArray((array)$this->getState($index));
                $this[static::PHASE_STATE_CONTAINER][$index] = $state;
            }
            # - Execute closure
            $result = $_closure($this);
            # - Save current state to storage
            $this->_cache->set($identifier, (array)$this);
            # - return result
            return $result;
        });
    }

    /**
     * @inheritdoc
     */
    public function synchronize(array $_data = null): ModelInterface
    {
        $identifier = $this->getIdentifier();
        $this->_cache->has($identifier)
            ? $this->exchangeArray($this->_cache->get($identifier))
            : ($_data && $this->exchangeArray($_data));
        # - Initialize global state
        $this(AbstractPhase::global);

        return $this;
    }

    /**
     * Flash action markers
     *
     * @return void
     */
    protected function flush() : void
    {
        $this->_initialize = false;
        $this->_process = false;
    }

}
